<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

			new AppBundle\AppBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new UserBundle\UserBundle(),
            new DashboardBundle\DashboardBundle(),
			new SettingsBundle\SettingsBundle(),
            new SqlmapBundle\SqlmapBundle(),
			new ArusProjectBundle\ArusProjectBundle(),
            new ArusHostBundle\ArusHostBundle(),
            new ArusDomainBundle\ArusDomainBundle(),
            new ArusServerBundle\ArusServerBundle(),
			new ArusEntityAlertBundle\ArusEntityAlertBundle(),
            new ArusEntityTaskBundle\ArusEntityTaskBundle(),
            new ArusEntityCommentBundle\ArusEntityCommentBundle(),
            new ArusTechnologyBundle\ArusTechnologyBundle(),
            new ChangelogBundle\ChangelogBundle(),
            new HelpBundle\HelpBundle(),
            new ArusEntityTechnologyBundle\ArusEntityTechnologyBundle(),
            new ArusEntityLootBundle\ArusEntityLootBundle(),
            new ArusTaskBundle\ArusTaskBundle(),
            new ArusTaskCallbackBundle\ArusTaskCallbackBundle(),
            new ArusHostServerBundle\ArusHostServerBundle(),
            new ArusBucketBundle\ArusBucketBundle(),
            new ArusEntityAttachmentBundle\ArusEntityAttachmentBundle(),
            new ArusRequestBundle\ArusRequestBundle(),
            new ArusServerServiceBundle\ArusServerServiceBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'), true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }
}
