version: '3.8'
services:
    frontend:
        container_name: frontend
        build:
            context: .
            dockerfile: Dockerfile
        volumes:
            - './angular:/app'
        ports:
            - 3001:3000
        environment:
            - CHOKIDAR_USEPOLLING=true
    backend:
        container_name: backend
        build:
            context: .
            dockerfile: Dockerfile
        volumes:
            - './api:/app'
        ports:
            - 3001:3000
        environment:
            - CHOKIDAR_USEPOLLING=true