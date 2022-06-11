# Haplotype Map JS
A library for visualizing haplotype data in the form a colored matrix.

## Goal

This fork is **focused on settings to make this web-app compatible with Drupal/Tripal**. All credit for this web application should remain with the original developers: Venkat Bandi in Dr. Carl Gutwin's Human Computer Interaction Lab at the University of Saskatchewan.

### Original Source

For any difficulties with the visualization directly, please go back to the original repository: https://github.com/kiranbandi/haplotype-map

## Docker

To test this web app using docker:

```
docker build ./ --tag=hapmap
docker run -dit --publish=80:80 --name=hapmap hapmap:latest
```

Then go to http://localhost to see the application.

NOTE: For development:

```
git clone https://github.com/UofS-Pulse-Binfo/haplotype-map.git
cd haplotype-map
docker build ./ --tag=hapmap
docker run -dit --publish=80:80  --volume=`pwd`:/app --name=hapmap hapmap:latest
```

Then you can make changes in your local clone and run the following command to see them reflected in the docker:

```
docker exec -it hapmap npm install
docker exec -it hapmap npm run build --webPath=""
```

## Tips

- You can set the web path to the data dir when building your application. This is important if the app is having trouble finding your data. For example, if this application is served from http://www.example.ca/haplotype-map then you would pass that information in as follows:

```
npm run build --webPath="haplotype-map/"
```
