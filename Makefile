# —— Inspired by ———————————————————————————————————————————————————————————————
# https://www.strangebuzz.com/en/snippets/the-perfect-makefile-for-symfony

# Setup ————————————————————————————————————————————————————————————————————————

# Parameters
SHELL         = bash
ME            = $(shell whoami)

PORT          = 8091
NUMPROC := $(shell grep -c ^processor /proc/cpuinfo)
# Only take half as many processors as available
NPROC := $(shell echo "$(NUMPROC)/2"|bc)
NPROC := 1

# Image
APP_NAME     = fastapi-sample
OCI_REGISTRY = 783876277037.dkr.ecr.eu-west-3.amazonaws.com
# OCI_REGISTRY = registry.gitlab.com/jusmundi-group/proof-of-concept
AWS_REGION   = eu-west-3
OCI_IMAGE := $(OCI_REGISTRY)/$(APP_NAME)
OCI_TAG := $${OCI_TAG:-"1.2.3"}
IMAGE_NEXT_TAG := $${OCI_IMAGE_TAG:-"latest"}
IMAGE := $(OCI_IMAGE):$(OCI_TAG)

TRIVY_VULN_TYPE = "os,library"
TRIVY_SECURITY_CHECKS = "vuln,config,secret"
TRIVY_GLOBAL_SECURITY_CHECKS = --security-checks ${TRIVY_SECURITY_CHECKS} --vuln-type ${TRIVY_VULN_TYPE}
TRIVY_ARGS = --skip-dirs .direnv --skip-dirs .venv --skip-dirs ./node_modules --skip-dirs /usr/local/lib/python3.12/dist-packages/ansible/galaxy/ --skip-dirs /home/ubuntu/.local/lib/python3.12/site-packages/awscli/ --skip-dirs /home/ubuntu/.local/share/virtualenvs/ --skip-dirs /home/ubuntu/.local/lib/python3.12/site-packages/rsa/ --skip-dirs /home/ubuntu/.local/lib/python3.12/site-packages/botocore/data/ --skip-dirs /usr/lib/node_modules/ --skip-files /usr/local/bin/container-structure-test
CS_SEVERITY_REPORT_THRESHOLD = "HIGH,CRITICAL"

# You can set these variables from the command line, and also
# from the environment for the first two.
SPHINXOPTS    ?=
SPHINXBUILD   ?= sphinx-build
SOURCEDIR     = docs/source
BUILDDIR      = build

# Executables: local only
# DOCKER        = docker

GIT_BRANCH = $$(git symbolic-ref --short HEAD)
DOCKER_COMPOSE_UP = "export GIT_BRANCH=$(GIT_BRANCH) && docker-compose up"
DOCKER_COMPOSE_DOWN = "export GIT_BRANCH=$(GIT_BRANCH) && docker-compose down"
DOCKER_RUN = "export GIT_BRANCH=$(GIT_BRANCH) && docker-compose run"

# Misc
.DEFAULT_GOAL = build
.PHONY       =  # Not needed here, but you can put your all your targets to be sure
	            # there is no name conflict between your files and your targets.

## —— 🐝 The Strangebuzz Docker Makefile 🐝 ———————————————————————————————————

_welcome: ## Print a Welcome screen
	curl -s https://raw.githubusercontent.com/AlbanAndrieu/AlbanAndrieu/master/welcome.txt

.PHONY: help Makefile
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
	@$(SPHINXBUILD) -M help "$(SOURCEDIR)" "$(BUILDDIR)" $(SPHINXOPTS) $(O)

.PHONY: help Makefile
# Catch-all target: route all unknown targets to Sphinx using the new
# "make mode" option.  $(O) is meant as a shortcut for $(SPHINXOPTS).
%: Makefile
	@$(SPHINXBUILD) -M $@ "$(SOURCEDIR)" "$(BUILDDIR)" $(SPHINXOPTS) $(O)

# jupyter: Spins up a jupyter instance hosted at http://127.0.0.1:8888/
jupyter:
	@"$(DOCKER_COMPOSE_DOWN)"
	@"$(DOCKER_COMPOSE_UP)"

prepare-pipenv:
	@echo "Setting up pipenv and installing packages"
	@which pipenv || python3 -m pip install pipenv
	@python -m pipenv install --dev --site-packages

setup-jupyter-local: prepare-pipenv
	@echo "Setting up local Jupyter"
#	@curl -L -O "https://github.com/conda-forge/miniforge/releases/download/4.12.0-2/Miniforge3-Linux-x86_64"
#	@bash Miniforge3-Linux-x86_64 -u -b -f
#	@rm Miniforge3-Linux-x86_64
#	@pipenv --python=${HOME}/mambaforge/bin/python install
	@curl -L -O "https://repo.anaconda.com/miniconda/Miniconda3-latest-Linux-x86_64.sh"
	@chmod +x Miniconda3-latest-Linux-x86_64.sh
	@bash Miniconda3-latest-Linux-x86_64.sh -u -b -f
	@rm Miniconda3-latest-Linux-x86_64.sh
	@pipenv --python=${HOME}/miniconda3/bin/python install
	@echo "Data Science environment successfully created"

jupyter-local:
	@echo "Running local Jupyter"
	@pipenv run jupyter-lab

setup-jupyter-local-no-mamba: prepare-pipenv
	@echo "Setting up local Jupyter without mamba"
	@pipenv install

## —— All 🎵 ———————————————————————————————————————————————————————————————
.PHONY: all
all: down clean build up test

## —— Clean Docker 🧹🐳💩 ———————————————————————————————————————————————————————————————
.PHONY: clean-docker
clean-docker:
	@echo "=> Cleaning image..."
	@docker rmi $(IMAGE)

## —— Clean Docker Docker 🧹🐳💩 ———————————————————————————————————————————————————————————————
.PHONY: clean-docker-compose
clean-docker-compose:
	@echo "Cleaning things up..."
	@"$(DOCKER_COMPOSE_DOWN)" -v
	@docker system prune -f

## —— Clean 🧹 ———————————————————————————————————————————————————————————————
.PHONY: clean
clean: clean-docker-compose clean-docker

## —— Formating 🧪🔗 ———————————————————————————————————————————————————————————————
.PHONY: fmt
fmt: ## Run formating
	@echo "=> Executing formating..."
	shfmt -i 2 -ci -w *.sh || true
	# ansible-lint --write ./
	# ./hclfmt-all.sh

## —— Docker Builder 🐳🚂 ————————————————————————————————————————————————————————————————
.PHONY: build-docker-base
build-docker-base:  ## Build base container with docker
	@echo "=> Building builder image..."
	docker build -t $(IMAGE) --target builder-base --secret id=CI_JOB_TOKEN,env=CI_PIP_GITLABJUSMUNDI_TOKEN --secret id=read-npm-token,env=CI_JOB_TOKEN --secret id=npmrc,src=$${HOME}/.npmrc --build-arg ENV=dev --build-arg CI_JOB_TOKEN=$${CI_JOB_TOKEN} --build-arg CI_PIP_GITLABJUSMUNDI_TOKEN=$${CI_PIP_GITLABJUSMUNDI_TOKEN} -f Dockerfile .

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
.PHONY: build-docker
build-docker:  ## Build container with docker
	@echo "=> Building image..."
	docker build --secret id=CI_JOB_TOKEN,env=CI_PIP_GITLABJUSMUNDI_TOKEN --secret id=read-npm-token,env=CI_JOB_TOKEN --build-arg ENV=dev -t $(IMAGE) .

## —— Buildah Docker 🐶🐳 ————————————————————————————————————————————————————————————————
.PHONY: build-buildah-docker
build-buildah-docker: ## Build container with buildah
	@echo "=> Building image..."
	buildah bud -t $(IMAGE) --build-arg CI_PIP_GITLABJUSMUNDI_TOKEN=$${CI_PIP_GITLABJUSMUNDI_TOKEN} .

## —— Build 🚀 —————————————————————————————————————————————————————————————————
.PHONY: build
build: build-docker

## —— Up Docker ✅🐳 —————————————————————————————————————————————————————————————————
.PHONY: up-docker
up-docker:
	@echo "up docker"
	docker run -it $(IMAGE)

## —— Up Python ✅🐍 —————————————————————————————————————————————————————————————————
.PHONY: up-python
up-python:
	@echo "python -m scripts toto.csv"
	@echo "up python http://0.0.0.0:$(PORT)/health"
	python -m server_app

## —— Up Python ✅🦄 —————————————————————————————————————————————————————————————————
.PHONY: up-uvicorn
up-uvicorn:
	@echo "up uvicorn http://0.0.0.0:$(PORT)/v1/ping"
	@echo ".venv/bin/uvicorn server_app:app --reload --workers 1 --host 0.0.0.0 --port $(PORT)"
	.venv/bin/uvicorn server_app:app --reload --workers $(NPROC) --host 0.0.0.0 --port $(PORT) \
	--loop uvloop --http httptools \
	--timeout-keep-alive 5 \
  --limit-concurrency 1000 \
  --backlog 2048

#--loop uvloop and --http httptools cut per-request overhead.
#--workers 2*CPU+1 gives you true parallelism despite the GIL.
#--timeout-keep-alive 5 frees idle connections quickly under load.
#--limit-concurrency and --backlog prevent thundering herds from drowning your box.

## —— Up Python App ✅🦄 —————————————————————————————————————————————————————————————————
.PHONY: up-gunicorn
up-gunicorn:
	@echo "up gunicorn http://0.0.0.0:$(PORT)/v1/ping"
	# @echo ".venv/bin/ddtrace-run .venv/bin/gunicorn server_all:app --reload --name fastapi-sample --workers 1 -k uvicorn_worker.UvicornWorker --bind 0.0.0.0:$(PORT) --logger-class=nabla.utils.log_config.JMGunicornLogger --log-level info --access-logfile - --statsd-host localhost:8125"
	.venv/bin/ddtrace-run .venv/bin/gunicorn server_all:app --reload --name fastapi-sample -k uvicorn_worker.UvicornWorker \
	--workers $(NPROC) \
	--max-requests 5000 --max-requests-jitter 500 \
    --graceful-timeout 30 --timeout 60 --keep-alive 5 \
    --threads 1 --worker-connections 1000 \
	--bind 0.0.0.0:$(PORT) --log-level info --access-logfile -


## —— Up Python App MCP ✅ —————————————————————————————————————————————————————————————————
.PHONY: up-mcp
up-mcp:
	@echo "up mcp http://0.0.0.0:8000/mcp"
	fastmcp run server_mcp.py

## —— Up ✅ —————————————————————————————————————————————————————————————————
.PHONY: up
up: up-gunicorn # Serve up (gunicorn)

.PHONY: down
down:
	@echo "down"

.PHONY: run
run: down up

## —— Doc 📜 —————————————————————————————————————————————————————————————————
.PHONY: doc
doc: ## Documentation
	@echo "=> Doc..."
	@sphinx-build ./docs/source _build --color -W -bhtml

## —— Lint 🧪 —————————————————————————————————————————————————————————————————
.PHONY: ruff
ruff: ## Linter ruff
	@echo "=> Linter flake8..."
	flake8 ./nabla/ tests  --config .flake8 --count --exit-zero --max-line-length=88 --max-complexity=12 --statistics
	# @echo "=> Linter black..."
	# @pipenv run black .
	@echo "=> Linter ruff..."
	@ruff format

## —— Debug 📜🐳 —————————————————————————————————————————————————————————————————
.PHONY: debug
debug: ## Enter container
	@echo "=> Debuging image..."
	@echo "docker run -it -u 0 --entrypoint /bin/bash --env CI_PIP_GITLABJUSMUNDI_TOKEN=$${CI_PIP_GITLABJUSMUNDI_TOKEN} --env CI_JOB_TOKEN=$${CI_JOB_TOKEN} $(IMAGE)"
	docker run -it --entrypoint /bin/bash --env CI_PIP_GITLABJUSMUNDI_TOKEN=$${CI_PIP_GITLABJUSMUNDI_TOKEN} --env CI_JOB_TOKEN=$${CI_JOB_TOKEN} $(IMAGE)

## —— Project 🐝🐳 ———————————————————————————————————————————————————————————————
.PHONY: start
start: ## Run container
	@echo "=> Executing image..."
	docker run -it -v /var/run/docker.sock:/var/run/docker.sock $(IMAGE)

## —— Tests Dive 🧪🐳🚨 —————————————————————————————————————————————————————————————————
.PHONY: test-dive
test-dive: ## Run Dive image tests
	@echo "=> Testing Dive image..."
	@echo "CI=true dive --ci --highestUserWastedPercent 0.1 --lowestEfficiency 0.9 --json docker-dive-stats.json $(IMAGE) 1>docker-dive.log 2>docker-dive-error.log"
	CI=true docker run --rm -it \
      -v /var/run/docker.sock:/var/run/docker.sock \
      -v "$(pwd)":"$(pwd)" \
      -w "$(pwd)" \
      -v "$(pwd)/.dive.yaml":"$(pwd)/.dive.yaml" \
      wagoodman/dive:latest --ci --json docker-dive-stats.json $(IMAGE)

## —— Tests Dive CI 🧪🐳🚨 —————————————————————————————————————————————————————————————————
.PHONY: test-dive-ci
test-dive-ci: ## Run Dive image tests for CI
	@echo "=> Testing Dive image..."
	CI=true dive --ci --highestUserWastedPercent 0.1 --lowestEfficiency 0.9 --json docker-dive-stats.json $(IMAGE)

## —— Tests Codeclimate 🧪🤖 —————————————————————————————————————————————————————————————————
.PHONY: test-codeclimate
test-codeclimate:
	@echo "=> Testing Codeclimate image..."
	@echo "codeclimate analyze"
	docker run \
  --interactive --tty --rm \
  --env CODECLIMATE_CODE="$PWD" \
  --volume "$PWD":/code \
  --volume /var/run/docker.sock:/var/run/docker.sock \
  --volume /tmp/cc:/tmp/cc \
  codeclimate/codeclimate analyze

## —— Tests Semgrep 🧪👽 —————————————————————————————————————————————————————————————————
.PHONY: test-semgrep
test-semgrep:
	@echo "=> Testing Semgrep image..."
	semgrep --config auto .

## —— Tests Nox 🧪⛓️ —————————————————————————————————————————————————————————————————
.PHONY: test-nox
test-nox:
	@echo "=> Testing python..."
	@echo "=> python -m pytest --cov=nabla --cov-fail-under=70"
	nox

## —— Tests Tox 🧪 —————————————————————————————————————————————————————————————————
.PHONY: test-tox
test-tox:
	@echo "=> Testing python..."
	@echo "=> tox --notest"
	tox py310

## —— Tests CST 🧪🕳️ —————————————————————————————————————————————————————————————————
.PHONY: test-cst
test-cst:
	@echo "=> Testing CST image..."
	@echo "/usr/local/bin/container-structure-test test --save -v info --image $(IMAGE) --config ./config.yaml"
	/usr/local/bin/container-structure-test test --image $(IMAGE) --config ./config.yaml

## —— Tests 🧪 —————————————————————————————————————————————————————————————————
.PHONY: test
test: test-tox test-dive test-codeclimate test-semgrep test-cst

## —— Tests Sast Docker 👮😈🐳 —————————————————————————————————————————————————————————————————
.PHONY: sast-docker
sast-docker:
	@echo "=> Scanning trivy image..."
	time trivy image --exit-code 1 --severity $(CS_SEVERITY_REPORT_THRESHOLD) $(TRIVY_GLOBAL_SECURITY_CHECKS) $(TRIVY_ARGS) --format table --output scan-report.md $(IMAGE) 1>docker-trivy.log 2>docker-trivy-error.log

## —— Tests Sast Fs Docker 👮😈️🐳 —————————————————————————————————————————————————————————————————
.PHONY: sast-fs-docker
sast-fs-docker:
	@echo "=> Scanning trivy filesystem..."
	time trivy filesystem --exit-code 2 --severity $(CS_SEVERITY_REPORT_THRESHOLD) $(TRIVY_GLOBAL_SECURITY_CHECKS) $(TRIVY_ARGS) --format table --output scan-report-fs.md . 1>docker-trivy-fs.log 2>docker-trivy-fs-error.log

## —— Tests Sast Buildah 👮😈🐶 —————————————————————————————————————————————————————————————————
.PHONY: sast-buildah
sast-buildah:
	@echo "=> Scanning trivy image..."
	rm -Rf "./archive/" || true
	mkdir "./archive/" || true
	buildah push $(IMAGE) docker-archive:./archive/built-with-buildah.tar:latest
	time trivy image --exit-code 1 --severity $(CS_SEVERITY_REPORT_THRESHOLD) $(TRIVY_GLOBAL_SECURITY_CHECKS) $(TRIVY_ARGS) --format table --output scan-report.md --input ./archive/built-with-buildah.tar 1>docker-trivy.log 2>docker-trivy-error.log

## —— Tests Sast 👮😈 —————————————————————————————————————————————————————————————————
.PHONY: sast
sast: sast-fs-docker ## Run Trivy sast

## —— Deploy Docker 💾🐳 ———————————————————————————————————————————————————————————————
.PHONY: deploy-docker
deploy-docker: ## Push to registry
	@echo "=> Tagging image..."
	docker tag $(IMAGE) $(OCI_IMAGE):$(IMAGE_NEXT_TAG)
	@echo "=> docker login registry.gitlab.com --username \$${GITLAB_PRIVATE_USERNAME} --password \$${GITLAB_FULL_PRIVATE_TOKEN}"
	@echo "=> aws ecr get-login-password --region \$${AWS_REGION:-"eu-west-3"} | docker login --username AWS --password-stdin \$${OCI_REGISTRY:-\"783876277037.dkr.ecr.eu-west-3.amazonaws.com\"} "
	@echo "=> Pushing image..."
	@echo "=> By Hand 👊 => docker push $(OCI_IMAGE):$(IMAGE_NEXT_TAG)"
	@echo "=> By Hand ✌ => docker push $(OCI_IMAGE):latest"

## —— Deploy Buildah 💾🐶 ———————————————————————————————————————————————————————————————
.PHONY: deploy-buildah
deploy-buildah: ## Push to registry
	@echo "=> Tagging image..."
	buildah tag $(IMAGE) $(OCI_IMAGE):$(IMAGE_NEXT_TAG)
	@echo "=> Pushing image..."
	@echo "=> TODO => buildah push $(OCI_IMAGE):$(IMAGE_NEXT_TAG)"
	@echo "=> TODO => buildah push $(OCI_IMAGE):latest"

## —— Deploy 💾👑 ———————————————————————————————————————————————————————————————
.PHONY: deploy
deploy: deploy-docker ## Push to registry
