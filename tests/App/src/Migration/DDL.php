<?php

declare(strict_types=1);

namespace Ep\Tests\App\Migration;

use Ep\Command\MigrateBuilder;
use Ep\Contract\MigrateInterface;

final class DDL implements MigrateInterface
{
    public function up(MigrateBuilder $builder): void
    {
        $builder->execute(<<<'DDL'
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT NULL,
  `username` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `password` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `age` int(11) DEFAULT '0',
  `sex` int(11) DEFAULT '1',
  `state` tinyint(4) DEFAULT '1',
  `birthday` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=162 DEFAULT CHARSET=utf8mb4;
CREATE TABLE `user_parent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
DDL);
    }

    public function down(MigrateBuilder $builder): void
    {
    }
}
