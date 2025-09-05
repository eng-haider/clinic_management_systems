#!/bin/bash

# Clean everything
echo "Cleaning build artifacts..."
rm -rf dist
rm -rf node_modules/.cache
rm -rf ~/.cache/vue-cli-service

# Clear npm cache
echo "Clearing npm cache..."
npm cache clean --force

# Reinstall dependencies (optional but thorough)
echo "Reinstalling dependencies..."
rm -rf node_modules
rm -f package-lock.json
npm install

# Build
echo "Building project..."
NODE_ENV=production vue-cli-service build

echo "Build complete!"