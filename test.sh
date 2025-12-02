#!/bin/env bash
docker build -t laravel-user-test . \
  && docker run --rm laravel-user-test
