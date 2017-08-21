# @todo Egyelőre nem itt van megvalósítva. Jobb lenne, ha itt lenne, csak utána kellene nézni, hogy felül lehet-e írni egy makefile-ban a tartgetet azzal, hogy include-dal behúzunk alá egy másik makefile-t, máshonnan.
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
