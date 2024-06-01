<?php

namespace Olympia\Kitpvp\managers\types;

use JsonException;
use Olympia\Kitpvp\Core;
use Olympia\Kitpvp\exceptions\CosmeticsException;
use Olympia\Kitpvp\managers\ManageLoader;
use Olympia\Kitpvp\player\OlympiaPlayer;
use Olympia\Kitpvp\player\skin\LegacySkinAdapter;
use pocketmine\entity\Skin;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use GdImage;

final class CosmeticsManager extends ManageLoader
{
    use SingletonTrait;

    public const COSMETIC_COSTUME = "costume";
    public const COSMETIC_CAPE = "cape";

    public const OBTAIN_PREFIX_PLAYERS = "players";
    public const OBTAIN_PREFIX_RANKS = "ranks";
    public const OBTAIN_PREFIX_BOX = "box";

    private const SIZE_64X32 = [64, 32];
    private const SIZE_64X64 = [64, 64];
    private const SIZE_128X128 = [128, 128];

    private array $cosmetics = [];

    private array $cosmeticsInfos = [];

    private array $categories = [];

    /** @var Skin[] $saveSkins */
    private array $saveSkins = [];

    /**
     * @throws JsonException|CosmeticsException
     */
    public function onInit(): void
    {
        $cosmeticsPath = $this->getCosmeticsPath();
        $created = is_dir($cosmeticsPath);

        if(!$created && !@mkdir($cosmeticsPath)) {
            throw new CosmeticsException("Impossible de créer le dossier cosmetics/, veuillez le créer manuellement.", CosmeticsException::ERR_CREATE_COSMETICS_DIR);
        }

        if($created !== is_dir($cosmeticsPath)) {
            Server::getInstance()->getLogger()->info("Système de cosmétique initialisé, veuillez mettre les cosmétiques (geometry et textures AVEC LE MÊME NOM) dans le chemin d'accès suivant : $cosmeticsPath\nLorsque vous avez ajouté un/des cosmétiques veuillez redémarrer le serveur pour les ajouter à celui-ci.");
        }

        foreach (scandir($cosmeticsPath) as $dir) {

            $dirPath = $cosmeticsPath . $dir;
            if(is_dir($dirPath) && $dir !== '.' && $dir !== '..') {

                $category = pathinfo($dir, PATHINFO_FILENAME);
                $this->registerCategory($category);

                foreach (scandir($dirPath) as $cosmeticFile) {

                    if($cosmeticFile !== '.' && $cosmeticFile !== '..') {

                        $cosmetic = pathinfo($cosmeticFile, PATHINFO_FILENAME);
                        $extension = pathinfo($cosmeticFile, PATHINFO_EXTENSION);

                        if ($extension === "png" && !in_array($cosmetic, $this->getCategoryCosmetics($category))) {

                            $this->addCategoryCosmetic($category, $cosmetic);
                        }
                    }
                }
            }
        }

        $cosmeticsConfig = new Config($this->getOwningPlugin()->getDataFolder() . "CosmeticsInfos.yml", Config::YAML);
        $cosmeticsInfos = $cosmeticsConfig->getAll();

        $missingCategories = array_diff($this->getCategoriesName(), array_keys($cosmeticsInfos));

        if(!empty($missingCategories)) {
            foreach ($missingCategories as $missingCategory) {
                $cosmeticsInfos[$missingCategory] = [];
            }
        }

        foreach ($this->getCategories() as $category => $cosmetics) {
            foreach ($cosmetics as $cosmetic) {

                if (!isset($cosmeticsInfos[$category][$cosmetic])) {
                    $cosmeticsInfos[$category][$cosmetic] = [
                        "category" => $category,
                        "type" => $this::COSMETIC_COSTUME,
                        "displayName" => $cosmetic,
                        "obtain" => null,
                    ];
                }else{
                    $cosmeticsInfos[$category][$cosmetic]["category"] = $category;

                    if (!isset($cosmeticsInfos[$category][$cosmetic]["type"]) || ($cosmeticsInfos[$category][$cosmetic]["type"] !== $this::COSMETIC_COSTUME && $cosmeticsInfos[$category][$cosmetic]["type"] !== $this::COSMETIC_CAPE)) {
                        $cosmeticsInfos[$category][$cosmetic]["type"] = $this::COSMETIC_COSTUME;
                    }

                    if (!isset($cosmeticsInfos[$category][$cosmetic]["displayName"])) {
                        $cosmeticsInfos[$category][$cosmetic]["displayName"] = $cosmetic;
                    }

                    if (!isset($cosmeticsInfos[$category][$cosmetic]["obtain"])) {
                        $cosmeticsInfos[$category][$cosmetic]["obtain"] = null;
                    }
                }
            }

            $removeCosmetics = array_diff(array_keys($cosmeticsInfos[$category]), $cosmetics);

            if(!empty($removeCosmetics)) {
                foreach ($removeCosmetics as $cosmetic) {
                    unset($cosmeticsInfos[$category][$cosmetic]);
                }
            }
        }

        $cosmeticsConfig->setAll($cosmeticsInfos);
        $cosmeticsConfig->save();
        $this->cosmeticsInfos = $cosmeticsInfos;

        $defaultSkinPath = $this->getDefaultSkinPath();

        if(!file_exists($defaultSkinPath)) {
            $plugin = Core::getInstance();
            $from = Core::getInstance()->getResourcePath("DefaultSkin.png");
            if (!@copy($from, $defaultSkinPath)) {
                $plugin->getLogger()->alert("Impossible d'importer le skin par défaut, veuillez le faire manuellement en lui donnant le nom \"DefaultSkin.png\"");
            }
        }

        TypeConverter::getInstance()->setSkinAdapter(new LegacySkinAdapter());
    }

    private function getCosmeticsPath(): string
    {
        return Core::getInstance()->getDataFolder() . "cosmetics/";
    }

    private function getCategoryCosmeticPath(string $category, string $cosmetic): string
    {
        return $this->getCosmeticsPath() . $category . "/" . $cosmetic;
    }

    public function getDefaultSkinPath(): string
    {
        return Core::getInstance()->getDataFolder() . "DefaultSkin.png";
    }

    public function updatePlayerCosmeticsInfos(OlympiaPlayer $player): void
    {
        foreach ($this->cosmeticsInfos as $category => $cosmeticsInfos) {
            if(!empty($cosmeticsInfos)) {
                foreach ($cosmeticsInfos as $cosmetic => $cosmeticInfos) {

                    if (is_null($cosmeticInfos["obtain"])) {
                        $player->addCosmetic($category, $cosmetic);
                    }else{

                        foreach ($cosmeticInfos["obtain"] as $prefix => $value) {

                            switch ($prefix) {

                                case $this::OBTAIN_PREFIX_PLAYERS:

                                    if(in_array(strtolower($player->getName()), $value)) {
                                        $player->addCosmetic($category, $cosmetic);
                                    }else{
                                        $player->removeCosmetic($category, $cosmetic);
                                    }
                                    break;

                                case $this::OBTAIN_PREFIX_RANKS:

                                    if (in_array(strtolower($player->getRankName()), $value)) {
                                        $player->addCosmetic($category, $cosmetic);
                                    }else{
                                        $player->removeCosmetic($category, $cosmetic);
                                    }
                                    break;
                            }
                        }
                    }
                }
            }
        }
    }

    public function filterCosmeticsInfosByObtainPrefix(string $prefix, array $categoriesCosmeticsInfos = []): array
    {
        if(empty($categoriesCosmeticsInfos)) {
            $categoriesCosmeticsInfos = $this->cosmeticsInfos;
        }
        $cosmeticsInfosFiltered = [];
        foreach ($categoriesCosmeticsInfos as $cosmeticsInfos) {
            if (!empty($cosmeticsInfos)) {
                foreach ($cosmeticsInfos as $cosmetic => $cosmeticInfos) {
                    if(!is_null($cosmeticInfos["obtain"]) && isset($cosmeticInfos["obtain"][$prefix])) {
                        $cosmeticsInfosFiltered[$cosmetic] = $cosmeticInfos;
                    }
                }
            }
        }
        return $cosmeticsInfosFiltered;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function getCategoriesName(): array
    {
        return array_keys($this->categories);
    }

    public function getCategoryCosmetics(string $category): array
    {
        return $this->categories[$category];
    }

    public function registerCategory(string $category): void
    {
        $this->categories[$category] = [];
    }

    public function addCategoryCosmetic(string $category, string $cosmetic): void
    {
        $this->categories[$category][] = $cosmetic;
    }

    public function getCategoryCosmeticInfos(string $category, string $cosmetic): array
    {
        return $this->cosmeticsInfos[$category][$cosmetic];
    }

    public function savePlayerSkin(string $player, Skin $skin): void
    {
        $this->saveSkins[strtolower($player)] = $skin;
    }

    public function getPlayerSaveSkin(string $player): ?Skin
    {
        return $this->saveSkins[strtolower($player)] ?? null;
    }

    /**
     * @throws JsonException
     */
    public function applyPlayerCosmetic(OlympiaPlayer $player, string $category, string $cosmetic, string $cosmeticType): void
    {
        $cosmeticImg = @imagecreatefrompng($this->getCategoryCosmeticPath($category, $cosmetic) . ".png");

        if (!$cosmeticImg) {
            $player->sendMessage(ConfigManager::getInstance()->getNested("messages.player-encounters-error"));
            return;
        }

        switch ($cosmeticType) {

            case $this::COSMETIC_COSTUME:

                $oldSkin = $player->getSkin();
                $newSkinData = $this->overlayingCostumeSkin($oldSkin->getSkinData(), $this->imgToBytes($cosmeticImg));
                if(!is_null($newSkinData)) {
                    $geometryName = "geometry.$cosmetic";
                    $geometryData = file_get_contents($this->getCategoryCosmeticPath($category, $cosmetic) . ".json", true);
                    $geometryDataAdjusted = json_encode($this->adjustGeometrySize(json_decode($geometryData, true), $this->getSkinDataSize($oldSkin->getSkinData())));
                    $newSkin = new Skin($oldSkin->getSkinId(), $newSkinData, $oldSkin->getCapeData(), $geometryName, $geometryDataAdjusted);
                    $player->setSkin($newSkin);
                    $player->sendSkin();
                }else{
                    $player->sendMessage(ConfigManager::getInstance()->getNested("messages.player-encounters-error"));
                }
                break;

            case $this::COSMETIC_CAPE:

                $oldSkin = $player->getSkin();
                $cape = $this->imgToBytes($cosmeticImg);
                $newSkin = new Skin($oldSkin->getSkinId(), $oldSkin->getSkinData(), $cape, $oldSkin->getGeometryName(), $oldSkin->getGeometryData());
                $player->setSkin($newSkin);
                $player->sendSkin();
                break;
        }
    }

    /**
     * @throws JsonException
     */
    public function removePlayerCosmetic(OlympiaPlayer $player, string $cosmeticType): void
    {
        switch ($cosmeticType) {

            case $this::COSMETIC_COSTUME:

                $saveSkin = $this->getPlayerSaveSkin($player->getName());
                $oldSkin = $player->getSkin();
                $newSkin = new Skin($saveSkin->getSkinId(), $saveSkin->getSkinData(),$oldSkin->getCapeData(), $saveSkin->getGeometryName(), $saveSkin->getGeometryData());
                $player->setSkin($newSkin);
                $player->sendSkin();
                break;

            case $this::COSMETIC_CAPE:

                $oldSKin = $player->getSkin();
                $newSkin = new Skin($oldSKin->getSkinId(), $oldSKin->getSkinData(), "", $oldSKin->getGeometryName(), $oldSKin->getGeometryData());
                $player->setSkin($newSkin);
                $player->sendSkin();
                break;
        }
    }

    public function adjustGeometrySize(array $geometry, array $size): array
    {
        if ($size === $this::SIZE_64X32) {

            $geometry["minecraft:geometry"][0]["bones"][5]["cubes"][0]["uv"][0] = 40;
            $geometry["minecraft:geometry"][0]["bones"][5]["cubes"][0]["uv"][1] = 16;
            unset($geometry["minecraft:geometry"][0]["bones"][6]["cubes"]);
            unset($geometry["minecraft:geometry"][0]["bones"][9]["cubes"]);
            $geometry["minecraft:geometry"][0]["bones"][13]["cubes"][0]["uv"][0] = 0;
            $geometry["minecraft:geometry"][0]["bones"][13]["cubes"][0]["uv"][1] = 16;
            unset($geometry["minecraft:geometry"][0]["bones"][14]["cubes"]);
            unset($geometry["minecraft:geometry"][0]["bones"][16]["cubes"]);
        }

        return $geometry;
    }

    public function overlayingCostumeSkin(string $bytesPlayer, string $bytesCosmetic): ?string
    {
        $size = $this->getSkinDataSize($bytesCosmetic);
        $L = $size[0];
        $l = $size[1];
        if ($l != 128 and $L != 128) {
            return null;
        }
        $size = $this->getSkinDataSize($bytesPlayer);
        $L = $size[0];
        $l = $size[1];
        $bytesOverlay = "";
        if ($l == 64 and $L == 64) {
            for ($i = 0; $i < 16384; $i += 256) {
                $bytesOverlay .= substr($bytesPlayer, $i, 256) . substr($bytesCosmetic, ($i * 2) + 256, 256);
            }
            $bytesOverlay .= substr($bytesCosmetic, 32768, 32768);
        } else if ($l == 32 and $L == 64) {
            for ($i = 0; $i < 8192; $i += 256) {
                $bytesOverlay .= substr($bytesPlayer, $i, 256) . substr($bytesCosmetic, ($i * 2) + 256, 256);
            }
            for ($i = 8192; $i < 16384; $i += 256) {
                $bytesOverlay .= str_repeat("\x00", 256) . substr($bytesCosmetic, ($i * 2) + 256, 256);
            }
            $bytesOverlay .= substr($bytesCosmetic, 32768, 32768);
        } else {
            $bytesOverlay = $this->getDefaultSkinBytes();
        }
        return $bytesOverlay;
    }

    public function getDefaultSkinBytes(): string
    {
        return $this->imgToBytes(@imagecreatefrompng($this->getDefaultSkinPath()));
    }

    public function getSkinDataSize(string $skinData): ?array
    {
        return match (strlen($skinData)) {
            Skin::ACCEPTED_SKIN_SIZES[0] => $this::SIZE_64X32,
            Skin::ACCEPTED_SKIN_SIZES[1] => $this::SIZE_64X64,
            Skin::ACCEPTED_SKIN_SIZES[2] => $this::SIZE_128X128,
            default => null,
        };
    }

    public function imgToBytes(GdImage $img): string
    {
        $bytes = '';
        for ($L = 0; $L < imagesy($img); $L++) {
            for ($l = 0; $l < imagesx($img); $l++) {
                $rgba = @imagecolorat($img, $l, $L);
                $r = ($rgba >> 16) & 0xff;
                $g = ($rgba >> 8) & 0xff;
                $b = $rgba & 0xff;
                $a = ((~(($rgba >> 24))) << 1) & 0xff;
                $bytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }
        @imagedestroy($img);
        return $bytes;
    }
}