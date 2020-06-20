<?php declare(strict_types=1);

namespace gstaSlimFooter;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class gstaSlimFooter extends Plugin
{
  public const SYSTEM_CONFIG_DOMAIN = 'gstaSlimFooter.config';
  public const SYSTEM_CONFIG_COLUMN = 'system_config.configurationKey';

  public function removeData($uninstallContext)
  {
    /** @var EntityRepositoryInterface $systemConfigRepository */
    $systemConfigRepository = $this->container->get('system_config.repository');
    $criteria = new Criteria();
    $filter = new ContainsFilter(self::SYSTEM_CONFIG_COLUMN, self::SYSTEM_CONFIG_DOMAIN);

    /** @var IdSearchResult $idSearchResult */
    $idSearchResult = $systemConfigRepository->searchIds($criteria->addFilter($filter), Context::createDefaultContext());

    $ids = array_map(static function ($id) {
      return ['id' => $id];
    }, $idSearchResult->getIds());

    if (empty($ids)) {
      parent::uninstall($uninstallContext);
      return;
    }

    $systemConfigRepository->delete(
        $ids, Context::createDefaultContext()
    );
  }

public function install(InstallContext $installContext): void
{
  $systemConfig = $this->container->get(SystemConfigService::class);
  $systemConfig->set(self::SYSTEM_CONFIG_DOMAIN . '.navPosition', '2');
  parent::install($installContext);
}

  public function uninstall(UninstallContext $uninstallContext): void
  {
    if ($uninstallContext->keepUserData()) {
      parent::uninstall($uninstallContext);
      return;
    }
    $this->removeData($uninstallContext);
    parent::uninstall($uninstallContext);
  }
}

