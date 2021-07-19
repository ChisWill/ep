<?php

declare(strict_types=1);

namespace Ep\Tests\App\Migration;

use Ep\Command\Helper\MigrateBuilder;
use Ep\Contract\MigrateInterface;

final class M20210523_2 implements MigrateInterface
{
    public static function getName(): string
    {
        return 'story';
    }

    public function up(MigrateBuilder $builder): void
    {
        $builder->createTable('story', [
            'id' => $builder->primaryKey(),
            'title' => $builder->string(50)->notNull(),
            'desc' => $builder->string(100)->defaultValue(''),
            'content' => $builder->text()
        ]);

        $builder->batchInsert('story', ['title', 'desc', 'content'], [
            ['第一章', '神狼的午后', '罗伊德在特别任务支援科总部见到了唐古拉门守备军的总指挥，克洛斯贝尔自治州警备队副司令索妮亚的委托，调查频频在首府周边发生的犬型魔兽袭人事件。'],
            ['第二章', '金之太阳、银之月', '克洛斯贝尔市的彩虹剧团名扬四海，拥有当家花旦，人称炎之舞姬的伊莉娅·普拉提耶，一次偶然中发现了潜力巨大的女孩丽霞·毛并且邀请其加入彩虹剧团，和自己共同排练新作品《金之太阳·银之月》。'],
            ['第三章', '克洛斯贝尔创立纪念庆典', '克洛斯贝尔自治州迎来5天的建州70周年祭，罗伊德一行人由于在恐吓信事件中的杰出表现而获得了一天的休假。4人各自行动。'],
            ['第四章', '悄然袭来的睿智', '矿山镇玛因兹的镇长向特别任务支援科求助，说一名矿工冈兹失踪数日。'],
            ['终章', '克洛斯贝尔最漫长的一日', '罗伊德等人欲再前往医科大学探寻蓝色药丸的成分，达德利告诉罗伊德，阿奈斯特在被捕后陷入了神智错乱，而约亚西姆一直是其主治医生，几人遂开始怀疑约亚西姆。'],
        ]);
    }

    public function down(MigrateBuilder $builder): void
    {
        $builder->dropTable('story');
    }
}
