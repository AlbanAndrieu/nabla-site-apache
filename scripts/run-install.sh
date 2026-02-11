#!/bin/bash
#set -xv
#set -eo pipefail

npm install --save @fortawesome/fontawesome-svg-core
npm install --save @fortawesome/free-solid-svg-icons
npm install --save @fortawesome/free-regular-svg-icons
npm install --save @fortawesome/free-brands-svg-icons

npm install
npx playwright install

# TODO Hugo, but conflciting with GitHub Page and Jekyll
# brew install hugo
# Add theme
# git submodule add https://github.com/theNewDynamic/gohugo-theme-ananke.git themes/ananke

npm run test -- --project="Mobile Safari" tests/homepage.spec.ts tests/responsive.spec.ts

exit 0
