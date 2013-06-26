CommandLine
===========

Zend Framework 1.8 Models and Mappers auto generate

Why using CommandLine ?
=======================

Every time that you will make a change on the database you don't need to change manually your model or your mapper
just run the model generator.
Instead, just run this script after you create your database or after any DB shema modification

What Files can you use or edit ?
===============================
If you need a model, buisnessObjet or an Entity just call it from models/Entity folder for example 
```php
new Application_Model_Entity_User();
```
 and put your custom code inside it.
Same thing for the mapper : 
```php
new Application_Model_Mapper_User();
```

DO NEVER CHANGE CODE ONLY INSIDE ENTITY OR MAPPER FOLDER!
---------------------------------------------------------

Default methods:
================
By default you can call all your model getters and setters alreday auto generated.
Like:
```php
 $user = new Application_Model_Entity_User();
 $user->setId(1)
  ->setName('Amine')
  ->setLastName('Cherif')
  ->setEnglishLanguageLevel(0)
  ->setPoor(TRUE);
```
And the default Mapper like 
```php
$userMapper = new Application_Model_Mapper_User();
$userMapper->save($user);
```
Your mapper have by default those methods:

```php
fetchAll() // Get an array of all buisness objects stored on the current table
populateForm($id) // Get the appropriated array to be populated by Zend_Form
find($id) // Similar to getElementById
delete($id) // If you guess what that method do, you are the next Steve Jobs ;)
save($buisnessObject) // so hard to explain :P
```

Installation
============

Put the CommandLine into Zend library Folder 
Copy and paste bin folder on your project folder


Use
===

* Open your console and go inside the bin folder

* and run: php modelgenerator.php

==> don't forget to change the Model folder permissions 

TO DO IN NEXT VERSION:
======================
Adding many to many and one to many relations 

License :
=========
I don't know but you can do allllllllllll you want with this script :D
About the author :
-----------------
* <a href="http://tn.linkedin.com/pub/mohamed-amine-cherif/19/a13/835/"> Me </a> ? Thank you what about you ?
* twitter <a href="https://twitter.com/maccherif"> maccherif </a>

Case you don't understand this script:
--------------------------------------
It's not a secret be smart and do like that you understand it's all what we do all the time else forget about your promotion !


Regards
-------
* Thank you for all your feed back and feel free to commit new crazy things.
* Sorry about my poor English (I need a cute English Teacher Girl Friend ;) )
* Good Luck!
