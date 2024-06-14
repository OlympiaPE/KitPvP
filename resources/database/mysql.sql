-- #!mysql
-- #{ loadServer
-- #    :name string
SELECT * FROM `servers` WHERE name = :name
-- #}
-- #{ createServer
-- #    :name string
-- #    :ip string
-- #    :port string
-- #    :server_data string []
-- #    :players_data string []
INSERT INTO `servers` (name, ip, port, server_data, players_data) VALUES (:name, :ip, :port, :server_data, :players_data)
-- #}
-- #{ saveServerData
-- #    :name string
-- #    :server_data string
-- #    :players_data string
UPDATE `servers` SET `server_data` = :server_data, `players_data` = :players_data WHERE `name` = :name
-- #}
-- #{ setServerData
-- #    :name string
-- #    :server_data string
UPDATE `servers` SET `server_data` = :server_data WHERE `name` = :name
-- #}
-- #{ setPlayersData
-- #    :name string
-- #    :players_data string
UPDATE `servers` SET `players_data` = :players_data WHERE `name` = :name
-- #}