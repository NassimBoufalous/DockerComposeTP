NAME=exo1
COMPOSE=docker compose -p $(NAME) -f docker-compose.yml

all:
	$(COMPOSE) up --build

clean:
	$(COMPOSE) down

fclean:
	$(COMPOSE) down --rmi all

re: fclean all
