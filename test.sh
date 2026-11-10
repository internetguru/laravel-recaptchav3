#!/bin/env bash
docker build -t laravel-recaptchav3-test . \
  && docker run --rm laravel-recaptchav3-test
