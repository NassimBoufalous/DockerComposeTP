NAME=exo2
COMPOSE=docker compose -p $(NAME) -f docker-compose.yml

all:
	$(COMPOSE) up --build

clean:
	$(COMPOSE) down

fclean:
	$(COMPOSE) down --rmi all

purge:
	$(COMPOSE) down
	powershell -Command "Remove-Item -Force .\data\app.db -ErrorAction SilentlyContinue"


re: fclean all
