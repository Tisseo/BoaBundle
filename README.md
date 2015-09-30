BOA
===

Description
-----------

BoaBundle for French acronym "Base Offre Affrêtée" is a back office application
used in [TID project](https://github.com/Tisseo/TID) and is providing
multiple functionalities in order to manage a
[TID database](https://raw.githubusercontent.com/Tisseo/TID/master/Diagramme.jpg).

This bundle is only working with [CanalTP](https://github.com/CanalTP)
[NMM](https://github.com/CanalTP/NmmPortalBundle) portal.

The BoaBundle is linked to [BoaBridgeBundle](https://github.com/Tisseo/BoaBridgeBundle)
in order to integrate the application in NMM portal.

Requirements
------------

- PHP 5.3+
- Symfony 2.6.x
- https://github.com/CanalTP/NmmPortalBundle
- https://github.com/Tisseo/TID
- https://github.com/Tisseo/EndivBundle
- https://github.com/Tisseo/CoreBundle

Installation
------------

This installation guide assumes that you already have installed a TID stack
(postgresql database) and have a functional NMM portal.

1. composer.json

You need to declare some repositories and requirements in the composer.json file.

```
"repositories": [
    {
        "type": "git",
        "url": "https://github.com/Tisseo/EndivBundle.git"
    },
    {
        "type": "git",
        "url": "https://github.com/Tisseo/CoreBundle.git"
    },
    {
        "type": "git",
        "url": "https://github.com/Tisseo/BoaBundle.git"
    },
    {
        "type": "git",
        "url": "https://github.com/Tisseo/BoaBridgeBundle.git"
    },
    //...
],
"require": {
    "tisseo/endiv-bundle": "dev-master",
    "tisseo/core-bundle": "dev-master",
    "tisseo/boa-bundle": "dev-master",
    "tisseo/boa-bridge-bundle": "dev-master"
    // ...
}
```

2. AppKernel.php

```
$bundles = array(
    new Tisseo\EndivBundle\TisseoEndivBundle(),
    new Tisseo\CoreBundle\TisseoCoreBundle(),
    new Tisseo\BoaBridgeBundle\TisseoBoaBridgeBundle(),
    new Tisseo\BoaBundle\TisseoBoaBundle(),
    // ...
);
```

Configuration
-------------

Check [EndivBundle](https://github.com/Tisseo/EndivBundle) configuration to provide a correct mapping
with TID database and allow BoaBundle to manage it.

Contributing
------------

- Vincent Passama - vincent.passama@gmail.com
- Rodolphe Duval - rdldvl@gmail.com
- Pierre-Yves Claitte - pierre.cl@gmail.com

Todo
----

- Add functional documentation
