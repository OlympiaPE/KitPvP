<?php

namespace Olympia\Kitpvp\entities\skin;

use Exception;
use JsonException;
use Olympia\Kitpvp\managers\Managers;
use pocketmine\entity\InvalidSkinException;
use pocketmine\entity\Skin;
use pocketmine\network\mcpe\convert\LegacySkinAdapter as VanillaLegacySkinAdapter;
use pocketmine\network\mcpe\protocol\types\skin\SkinData;

class LegacySkinAdapter extends VanillaLegacySkinAdapter
{
    /**
     * @throws JsonException
     * @throws Exception
     */
    public function fromSkinData(SkinData $data): Skin
    {
        $manager = Managers::COSMETICS();
        $skinSize = $manager->getSkinDataSize($data->getSkinImage()->getData());
        if(
            $data->isPersona() ||
            $skinSize === [128, 128] ||
            is_null($skinSize)
        ) {
            return new Skin(
                "Standard_Custom",
                $manager->getDefaultSkinBytes()
            );
        }

        $capeData = $data->isPersonaCapeOnClassic() ? "" : $data->getCapeImage()->getData();

        $resourcePatch = json_decode($data->getResourcePatch(), true);
        if(is_array($resourcePatch) && isset($resourcePatch["geometry"]["default"]) && is_string($resourcePatch["geometry"]["default"])) {
            $geometryName = $resourcePatch["geometry"]["default"];
        }else{
            throw new InvalidSkinException("Missing geometry name field");
        }

        return new Skin($data->getSkinId(), $data->getSkinImage()->getData(), $capeData, $geometryName, $data->getGeometryData());
    }
}