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
