NAME=exo4
COMPOSE=docker compose -p $(NAME) -f docker-compose.yml

all:
	$(COMPOSE) up --build

clean:
	$(COMPOSE) down

fclean:
	$(COMPOSE) down --rmi all -v --remove-orphans

purge_bdd:
	$(COMPOSE) down
	docker volume rm -f exo4_pgdata exo4_pgadmin_data || exit 0

purge_all:
	$(COMPOSE) down --rmi all -v --remove-orphans
	$(COMPOSE) up --build

re: fclean all
