# SRI plugin for Craft CMS

Generate the SRI (Subresource Integrity Hash) for remote JS/CSS files.

## Installation

To install SRI, follow these steps:

1. Download & unzip the file and place the `sri` directory into your `craft/plugins` directory
2.  -OR- do a `git clone https://github.com/sjelfull/Craft-SRI.git` directly into your `craft/plugins` folder.  You can then update it with `git pull`
3. Install plugin in the Craft Control Panel under Settings > Plugins
4. The plugin folder should be named `sri` for Craft to see it.  GitHub recently started appending `-master` (the branch name) to the name of the folder for zip file downloads.

SRI works on Craft 2.4.x and Craft 2.5.x.

## SRI Overview

SRI is a new W3C specification that allows web developers to ensure that resources hosted on third-party servers have not been tampered with. Use of SRI is recommended as a best-practice, whenever libraries are loaded from a third-party source.

Learn more about how to use [subresource integrity](https://developer.mozilla.org/en-US/docs/Web/Security/Subresource_Integrity) on MDN.

## Using SRI

You can apply the `sri` filter to either a `<script>` or a `<link>` tag, like so:

```{{ '<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>' | sri }}```

```{{ '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" media="screen" title="no title" charset="utf-8">' | sri }}```

The request will be cached for 24 hours. You may override this in general.php by setting the duration like so:

```php
    'sriCacheDuration' => 3600 * 24, // 24 hours by default
```

## SRI Roadmap

- Add option for calculating hashes with multiple algorithms
- Make cache duration configurable.

## SRI Changelog

### 1.0.0 -- 2016.03.20

* Initial release

Brought to you by [Fred Carlsen](http://sjelfull.no)
