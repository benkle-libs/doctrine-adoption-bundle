Doctrine Adoption Bundle
========================

A small set of services to make doctrines inheritance mapping more useful.


Installation
------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require benkle/doctrine-adoption-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Benkle\DoctrineAdoptionBundle\BenkleDoctrineAdoptionBundle(),
        );

        // ...
    }

    // ...
}
```

Usage
-----

### Step 1: Define a parent entity

```php
/**
 * Class Document
 * @package AppBundle\Entity
 * @Entity()
 * @Table(name="documents")
 * @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="type", type="string")
 * @DiscriminatorMap({"document" = "AppBundle\Entity\Document"})
 */
class Document
{
    /**
     * @Id()
     * @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @Column(type="string")
     */
    public $name;
}
```

I recommend that you...
 * ...use `JOINED` as inheritance type, as you probably want to add children **after** the database was first created, and updating a large table can be expensive. Plus you'll avoid column name clashes.
 * ...at least declare the discriminator map with the parent as only entry. Mostly for clarity's sake.

### Step 2: Define a child

```php
/**
 * Class TextDocument
 * @package Demo\TextDocumentBundle\Entity
 * @Entity()
 * @Table(name="text_documents")
 */
class TextDocument extends Document
{
    /**
     * @Column(type="text")
     */
    public $text;
}
```

### Step 3: Create a service definition, so your child can be adopted

```yaml
services:
    demo_text_document_bundle.text_document:
        class: Demo\TextDocumentBundle\Entity\TextDocument
        public: false
        tags:
            - name: benkle.doctrine.adoption.child
              of: AppBundle\Entity\Document
              discriminator: text_document
```

Just like Twig extensions, your entities should be declared as private services.
The parameters are simple:
 * `name`: The tag name (must be `benkle.doctrine.adoption.child`).
 * `of`: The full name of the parent class (think *child **of***).
 * `discriminator`: The value for the discriminator column.


### Step 4: Clear the cache

```bash
$ php bin/console cache:clear
```

### Step 5: Create or update db

```bash
$ php bin/console doctrine:schema:create
```
or

```bash
$ php bin/console doctrine:schema:update --force
```
