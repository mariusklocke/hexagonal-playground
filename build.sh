#!/usr/bin/env bash
set -e

if [[ -z "${DOCKER_REPO}" ]]; then
  DOCKER_REPO="mklocke/liga-manager-api"
fi

if [[ -z "${GITHUB_REF}" ]]; then
  TAG="latest"
else
  TAG="${${GITHUB_REF}/refs\/heads\//}"
fi

cleanup() {
    echo 'Cleanup: Removing containers ...'
    DOCKER_REPO=$DOCKER_REPO TAG=$TAG docker-compose -f docker-compose.build.yml down -v
}

# Build image
docker build -f docker/php/Dockerfile -t $DOCKER_REPO:$TAG -q .

# Make sure we clean up running containers in case of error
trap cleanup EXIT

# Launch containers
DOCKER_REPO=$DOCKER_REPO TAG=$TAG docker-compose -f docker-compose.build.yml up -d

# Run deptrac
DOCKER_REPO=$DOCKER_REPO TAG=$TAG docker-compose -f docker-compose.build.yml exec php bin/deptrac.phar --no-progress

# Run tests
DOCKER_REPO=$DOCKER_REPO TAG=$TAG docker-compose -f docker-compose.build.yml exec -e CI php run-tests.sh
