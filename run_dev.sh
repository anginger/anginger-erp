#!/bin/sh
# Copyright 2025 Anginger Platform
# 
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
# 
#     http://www.apache.org/licenses/LICENSE-2.0
# 
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

set -e
export YII_ENV=dev YII_DEBUG=true

# The frontend is server-rendered PHP + Alpine.js, so only the API/dev server is needed.
sh -c "cd api && composer install && php console.php migrate --interactive=0 && cd web && php -S 127.0.0.1:1270 index.php" &

wait
