# Stage 1: Compile and Build angular codebase
FROM node:14 as base
RUN mkdir -p /app
WORKDIR /app
COPY package.json /app
RUN npm install
COPY . /app
EXPOSE 80 4200 49153
RUN npm run build --prod