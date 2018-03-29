#!/usr/bin/env bash

cd {{ project_path }}
{{ commands | join("\n") }}
