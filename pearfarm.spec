<?php

$spec = Pearfarm_PackageSpec::create(array(Pearfarm_PackageSpec::OPT_BASEDIR => dirname(__FILE__)))
             ->setName('reacton')
             ->setChannel('TODO: Release channel here')
             ->setSummary('TODO: One-line summary of your PEAR package')
             ->setDescription('TODO: Longer description of your PEAR package')
             ->setReleaseVersion('0.0.1')
             ->setReleaseStability('alpha')
             ->setApiVersion('0.0.1')
             ->setApiStability('alpha')
             ->setLicense(Pearfarm_PackageSpec::LICENSE_MIT)
             ->setNotes('Initial release.')
             ->addMaintainer('lead', 'Wil Moore III', 'wilmoore', 'wil.moore+reacton@wilmoore.com')
             ->addGitFiles()
             ->addExecutable('reacton')
             ;
