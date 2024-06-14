<?php

namespace Olympia\Kitpvp\tasks\async;

use Olympia\Kitpvp\entities\Session;
use Olympia\Kitpvp\managers\Managers;
use Olympia\Kitpvp\managers\types\BoxsManager;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class VoteAsyncTask extends AsyncTask
{
    private string $key;
    private string $player;

    /**
     * @param string $key
     * @param string $player
     */
    public function __construct(string $key, string $player)
    {
        $this->key = $key;
        $this->player = $player;
    }

    public function onRun(): void
    {
        $url = "https://minecraftpocket-servers.com/api/?object=votes&element=claim&key=". $this->key ."&username=". $this->player;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === "1") {

            $url = "https://minecraftpocket-servers.com/api/?action=post&object=votes&element=claim&key=". $this->key ."&username=". $this->player;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $claimed = curl_exec($ch);
            curl_close($ch);

            $this->setResult($claimed);
        }else{

            $this->setResult("0");
        }
    }

    public function onCompletion(): void
    {
        $result = $this->getResult();

        /** @var ?Session $player */
        $player = Server::getInstance()->getPlayerExact($this->player);

        if(is_null($player)) return;

        if($result === "1") {

            Managers::BOXS()->giveKey($player, BoxsManager::BOX_VOTE);
            $player->sendMessage(Managers::CONFIG()->getNested("messages.vote"));
            $player->getServer()->broadcastMessage(str_replace(
                "{player}",
                $player->getDisplayName(),
                Managers::CONFIG()->getNested("messages.general-vote")
            ));
        }else{
            $player->sendMessage(Managers::CONFIG()->getNested("messages.not-voted"));
        }
    }
}