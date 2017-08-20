.PHONY: feature
feature:
	@echo "git checkout -b feature/${ARGS} origin/develop"

.PHONY: hotfix
hotfix:
	@echo "git checkout -b hotfix/${ARGS} origin/master"

.PHONY: push
push:
	@echo "git push"

.PHONY: publish
publish: push
	@echo "gitlab publish"
