README
======

Description
-----------

BoaBundle for French acronym "Base Offre Affrêtée" is used in public transport 
ENDIV project (https://github.com/Tisseo/TID).
This bundle is working with Symfony NMM application and use mapping information 
provided by EndivBundle (https://github.com/Tisseo/EndivBundle) in order to 
administrate ENDIV database and create new public transport commercial offers.

BoaBundle is aimed to work in NMM application environment.
(https://github.com/CanalTP/NmmPortalBundle)

Requirements
------------

- PHP 5.4.3
- https://github.com/Tisseo/TID
- https://github.com/Tisseo/EndivBundle 

Installation
------------

1. composer.json:

'''
    "repositories": [
        {   
            "type": "git",
            "url": "https://github.com/Tisseo/BoaBundle.git"
        },
        //...
    ],
    "require": {
        "tisseo/boa-bundle": "dev-master",
        // ...
    }
'''

2. AppKernel.php

'''
    $bundles = array(
        new Tisseo\BoaBundle\TisseoBoaBundle(),
        // ...
    );
'''

Configuration
-------------

You don't need to do this if you're working with the main bundle NMM which
provides all this configuration already.

Coming soon...

Contributing
------------

1. Vincent Passama - vincent.passama@gmail.com
2. Rodolphe Duval - rdldvl@gmail.com
3. Pierre-Yves Claitte - pierre.cl@gmail.com

TODO
----

- Add some information or a link to the whole ENDIV/NMM project documentation
- Add some configuration details
