.PHONY: commit push

commit_message := $(shell php artisan inspire)

commit:
	@echo "Committing with message: $(commit_message)"
	@git add .
	@git commit -m "$(commit_message)"

push:
	@git push origin main

auto: commit push
