CHANGELOG
=========

## 2017-09-15

- Add `1.1.0` version replace some commands name and add new commands:

| Old command        | New command    | Description |
| ------------------ | -------------- | ----------- |
| docker-compose-cmd | docker-compose | Docker compose command. This is shorter and more logical. |
| connect            | exec           | Shorter and more logical what does it |
| -                  | run            | This is a new command which run and not exec the command |
| sf                 | sf             | Change in base. Use exec instead of run because sometimes need the other docker service. Eg: `doctrine:*` commands need database! |

- Upgrade autocomplete
