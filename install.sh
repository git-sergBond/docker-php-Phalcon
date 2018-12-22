#!/bin/bash
sudo make dc-build
sudo make dc-up

#sudo make test-install
sudo make reinstall-backend

sudo create-db