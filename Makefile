MAKEFLAGS += --no-print-directory
PHP=php-cli

.: quickstart

boldunderline := \\033[1m\\033[4m
bold := \\e[1m
normal := \\033[0m
green := \\033[0;32m
yellow := \\033[0;33m
red := \\033[0;31m

quickstart:
	@echo "${bold}${red}"
	@echo "                                                                                 bbbbbbbb"
	@echo "             GGGGGGGGGGGGG  iiii          tttt          lllllll                  b::::::b "
	@echo "          GGG::::::::::::G i::::i      ttt:::t          l:::::l                  b::::::b  "
	@echo "        GG:::::::::::::::G  iiii       t:::::t          l:::::l                  b::::::b   "
	@echo "       G:::::GGGGGGGG::::G             t:::::t          l:::::l                   b:::::b    "
	@echo "      G:::::G       GGGGGGiiiiiiittttttt:::::ttttttt     l::::l   aaaaaaaaaaaaa   b:::::bbbbbbbbb"
	@echo "     G:::::G              i:::::it:::::::::::::::::t     l::::l   a::::::::::::a  b::::::::::::::bb"
	@echo "     G:::::G               i::::it:::::::::::::::::t     l::::l   aaaaaaaaa:::::a b::::::::::::::::b                                                                    "
	@echo "     G:::::G    GGGGGGGGGG i::::itttttt:::::::tttttt     l::::l            a::::a b:::::bbbbb:::::::b                                                                   "
	@echo "     G:::::G    G::::::::G i::::i      t:::::t           l::::l     aaaaaaa:::::a b:::::b    b::::::b                                                                   "
	@echo "     G:::::G    GGGGG::::G i::::i      t:::::t           l::::l   aa::::::::::::a b:::::b     b:::::b                                                                   "
	@echo "     G:::::G        G::::G i::::i      t:::::t           l::::l  a::::aaaa::::::a b:::::b     b:::::b                                                                   "
	@echo "      G:::::G       G::::G i::::i      t:::::t    tttttt l::::l a::::a    a:::::a b:::::b     b:::::b                                                                   "
	@echo "       G:::::GGGGGGGG::::Gi::::::i     t::::::tttt:::::tl::::::la::::a    a:::::a b:::::bbbbbb::::::b                                                                   "
	@echo "        GG:::::::::::::::Gi::::::i     tt::::::::::::::tl::::::la:::::aaaa::::::a b::::::::::::::::b                                                                    "
	@echo "          GGG::::::GGG:::Gi::::::i       tt:::::::::::ttl::::::l a::::::::::aa:::ab:::::::::::::::b                                                                     "
	@echo "             GGGGGG   GGGGiiiiiiii         ttttttttttt  llllllll  aaaaaaaaaa  aaaabbbbbbbbbbbbbbbb                                                                      "
	@echo "                                                                                                                                                                        "
	@echo "                                                                                                                                                                        "
	@echo "TTTTTTTTTTTTTTTTTTTTTTT  iiii                                          TTTTTTTTTTTTTTTTTTTTTTT                                                       kkkkkkkk           "
	@echo "T:::::::::::::::::::::T i::::i                                         T:::::::::::::::::::::T                                                       k::::::k           "
	@echo "T:::::::::::::::::::::T  iiii                                          T:::::::::::::::::::::T                                                       k::::::k           "
	@echo "T:::::TT:::::::TT:::::T                                                T:::::TT:::::::TT:::::T                                                       k::::::k           "
	@echo "TTTTTT  T:::::T  TTTTTTiiiiiii    mmmmmmm    mmmmmmm       eeeeeeeeeeeeTTTTTT  T:::::T  TTTTTTrrrrr   rrrrrrrrr   aaaaaaaaaaaaa      cccccccccccccccc k:::::k    kkkkkkk"
	@echo "        T:::::T        i:::::i  mm:::::::m  m:::::::mm   ee::::::::::::ee      T:::::T        r::::rrr:::::::::r  a::::::::::::a   cc:::::::::::::::c k:::::k   k:::::k "
	@echo "        T:::::T         i::::i m::::::::::mm::::::::::m e::::::eeeee:::::ee    T:::::T        r:::::::::::::::::r aaaaaaaaa:::::a c:::::::::::::::::c k:::::k  k:::::k  "
	@echo "        T:::::T         i::::i m::::::::::::::::::::::me::::::e     e:::::e    T:::::T        rr::::::rrrrr::::::r         a::::ac:::::::cccccc:::::c k:::::k k:::::k   "
	@echo "        T:::::T         i::::i m:::::mmm::::::mmm:::::me:::::::eeeee::::::e    T:::::T         r:::::r     r:::::r  aaaaaaa:::::ac::::::c     ccccccc k::::::k:::::k    "
	@echo "        T:::::T         i::::i m::::m   m::::m   m::::me:::::::::::::::::e     T:::::T         r:::::r     rrrrrrraa::::::::::::ac:::::c              k:::::::::::k     "
	@echo "        T:::::T         i::::i m::::m   m::::m   m::::me::::::eeeeeeeeeee      T:::::T         r:::::r           a::::aaaa::::::ac:::::c              k:::::::::::k     "
	@echo "        T:::::T         i::::i m::::m   m::::m   m::::me:::::::e               T:::::T         r:::::r          a::::a    a:::::ac::::::c     ccccccc k::::::k:::::k    "
	@echo "      TT:::::::TT      i::::::im::::m   m::::m   m::::me::::::::e            TT:::::::TT       r:::::r          a::::a    a:::::ac:::::::cccccc:::::ck::::::k k:::::k   "
	@echo "      T:::::::::T      i::::::im::::m   m::::m   m::::m e::::::::eeeeeeee    T:::::::::T       r:::::r          a:::::aaaa::::::a c:::::::::::::::::ck::::::k  k:::::k  "
	@echo "      T:::::::::T      i::::::im::::m   m::::m   m::::m  ee:::::::::::::e    T:::::::::T       r:::::r           a::::::::::aa:::a cc:::::::::::::::ck::::::k   k:::::k "
	@echo "      TTTTTTTTTTT      iiiiiiiimmmmmm   mmmmmm   mmmmmm    eeeeeeeeeeeeee    TTTTTTTTTTT       rrrrrrr            aaaaaaaaaa  aaaa   cccccccccccccccckkkkkkkk    kkkkkkk"
	@echo ""
	@echo "${green}Made with http://patorjk.com/software/taag/"
	@echo "${normal}${boldunderline}USAGE"
	@echo "${normal}"
	@echo "${green}${bold}make [OPTIONS] [COMMAND]"
	@echo ""
	@echo "${normal}${boldunderline}MAIN COMMANDS"
	@echo "${normal}"
	@echo " ${green}${bold}shell${normal}:   Get an interactive shell (inside the PHP-CLI container)."
	@echo " ${green}${bold}install${normal}: Install dependencies and create database (${red}Danger: it will drop entire DB!${normal})."
	@echo " ${green}${bold}docker-start${normal}: Starts docker containers."
	@echo " ${green}${bold}docker-stop${normal}: Stop docker containers."
	@echo " ${green}${bold}docker-restart${normal}: Re-starts docker containers."
	@echo ""

shell:
	@docker-compose exec -w "/var/www" $(PHP) bash

install:
	@docker-compose build
	@docker-compose up -d
	@docker-compose exec -w "/var/www" $(PHP) composer install
	@docker-compose exec -w "/var/www" $(PHP) bin/console doctrine:schema:drop --force
	@docker-compose exec -w "/var/www" $(PHP) bin/console doctrine:schema:create

docker-start:
	@docker-compose up -d

docker-stop:
	@docker-compose stop

docker-restart:
	@docker-compose stop
	@docker-compose up -d
