# Notes for maintainers

## Publishing a new release

1. Update the value of the `$pkgVersion` property in the main package `controller.php` file to a *stable* version (for example `1.0.0`)
2. Commit the change
3. Create a git tag (for example `v1.0.0`)
4. Push the change and the tags to GitHub
5. Publish [a new GitHub Release](https://github.com/concretecms-community-store/community_store/releases) (the `create-release-attachment.yml` GitHub Action will shortly attach the package .zip file to it automatically)
6. Update the value of the `$pkgVersion` property in the main package `controller.php` file to the next *development* version (for example `1.0.1-alpha1`)
7. Commit the change
8. Push the change to GitHub
