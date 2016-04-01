<?php
return array(

    //游戏行为列表
    'BEHAVE' => array(//通讯密钥

        /********************程序员行为********************/
        'test' => array('code' => 100, 'message' => '测试',),

        /********************自动行为********************/
        'platform' => array('code' => 800, 'message' => '平台活动',),
        'operation_activity' => array('code' => 801, 'message' => '运营活动',),
        'exchange' => array('code' => 997, 'message' => '兑换',),
        'gm' => array('code' => 998, 'message' => 'gm发送',),
        'auto_add' => array('code' => 999, 'message' => '自动增加',),

        /********************主动行为********************/

        //游戏
        'new_game' => array('code' => 1000, 'message' => '创角赠送',),
        'pay' => array('code' => 1001, 'message' => '充值',),
        'buy_gold' => array('code' => 1002, 'message' => '购买金币',),
        'buy_vality' => array('code' => 1003, 'message' => '购买体力',),
        'member_bonus' => array('code' => 1004, 'message' => '会员卡奖励',),
        'vip_bonus' => array('code' => 1005, 'message' => 'VIP奖励',),
        'mini_game_bonus' => array('code' => 1006, 'message' => '小游戏奖励',),
        'level_bonus_receive' => array('code' => 1007, 'message' => '等级奖励',),
        'vip_daily_bonus' => array('code' => 1008, 'message' => 'VIP每日奖励',),
        'vip_compensate_bonus' => array('code' => 1009, 'message' => 'VIP每日奖励补偿',),
        'first_pay_bonus' => array('code' => 1010, 'message' => '首冲奖励',),
        'buy_skill_point' => array('code' => 1011, 'message' => '购买技能点',),
        'pre_download_bonus' => array('code' => 1012, 'message' => '预下载奖励',),
        'survey_bonus' => array('code' => 1013, 'message' => '问卷调查奖励',),

        //伙伴模块
        'partner_upgrade' => array('code' => 2001, 'message' => '伙伴升阶',),
        'partner_skill_levelup' => array('code' => 2002, 'message' => '伙伴技能升级',),
        'partner_quest_complete' => array('code' => 2003, 'message' => '完成伙伴任务',),
        'partner_call' => array('code' => 2004, 'message' => '召唤伙伴',),
        'partner_set_force' => array('code' => 2005, 'message' => '设置伙伴战力',),
        'quest_partner_win' => array('code' => 2006, 'message' => '伙伴任务副本胜利',),
        'quest_partner_lose' => array('code' => 2007, 'message' => '伙伴任务副本失败',),
        'partner_awake' => array('code' => 2008, 'message' => '伙伴觉醒',),

        //装备模块
        'equip_strengthen' => array('code' => 3001, 'message' => '装备强化',),
        'equip_upgrade' => array('code' => 3002, 'message' => '装备升阶',),
        'equip_enchant_lock' => array('code' => 3003, 'message' => '装备附魔属性锁定',),
        'equip_enchant_offer' => array('code' => 3004, 'message' => '附魔献祭',),
        'equip_enchant' => array('code' => 3005, 'message' => '装备附魔',),
        'equip_enchant_diamond' => array('code' => 3006, 'message' => '水晶装备附魔',),
        'emblem_decompose' => array('code' => 3101, 'message' => '纹章分解',),
        'emblem_sell' => array('code' => 3102, 'message' => '纹章出售',),
        'emblem_combine' => array('code' => 3103, 'message' => '纹章合成',),
        'star_levelup' => array('code' => 3201, 'message' => '星位升级',),
        'star_reset' => array('code' => 3202, 'message' => '星位重置',),
        'star_baptize_gold' => array('code' => 3203, 'message' => '星位金币洗炼',),
        'star_baptize_diamond' => array('code' => 3204, 'message' => '星位水晶洗炼',),

        //道具模块
        'item_sell' => array('code' => 4001, 'message' => '道具出售',),
        'item_use' => array('code' => 4002, 'message' => '道具使用',),

        //商店模块
        'shop_buy_normal' => array('code' => 5001, 'message' => '购买普通商品',),
        'shop_buy_mystery' => array('code' => 5002, 'message' => '购买神秘商品',),
        'shop_buy_league' => array('code' => 5003, 'message' => '购买公会商品',),
        'shop_buy_hero' => array('code' => 5004, 'message' => '购买英雄商品',),
        'shop_buy_arena' => array('code' => 5005, 'message' => '购买竞技场商品',),
        'shop_buy_vip' => array('code' => 5007, 'message' => '购买VIP商品',),
        'shop_buy_vip_daily' => array('code' => 5008, 'message' => '购买每日VIP商品',),
        'shop_refresh_normal' => array('code' => 5101, 'message' => '刷新普通商店',),
        'shop_refresh_mystery' => array('code' => 5102, 'message' => '刷新神秘商店',),
        'shop_refresh_league' => array('code' => 5103, 'message' => '刷新公会商店',),
        'shop_refresh_hero' => array('code' => 5104, 'message' => '刷新英雄商店',),
        'shop_refresh_arena' => array('code' => 5105, 'message' => '刷新竞技场商店',),

        //任务模块
        'quest_complete' => array('code' => 6001, 'message' => '完成任务',),
        'quest_daily_complete' => array('code' => 6101, 'message' => '完成每日任务',),
        'achievement_complete' => array('code' => 6201, 'message' => '完成成就',),
        'activity_receive' => array('code' => 6301, 'message' => '领取活跃奖励',),

        //好友模块
        'friend_sendVality' => array('code' => 7001, 'message' => '赠送体力',),
        'friend_getVality' => array('code' => 7002, 'message' => '领取体力',),

        //公会模块
        'league_setup' => array('code' => 8001, 'message' => '新建公会',),
        'league_donate' => array('code' => 8002, 'message' => '公会捐赠',),
        'league_change_president' => array('code' => 8003, 'message' => '更换会长',),
        'league_battle_fight' => array('code' => 8004, 'message' => '开始公会战役',),
        'league_battle_win' => array('code' => 8005, 'message' => '公会战役胜利',),
        'league_battle_lose' => array('code' => 8006, 'message' => '公会战役失败',),
        'league_battle_buy_challenges' => array('code' => 8007, 'message' => '购买公会挑战次数',),
        'league_battle_activation_idol' => array('code' => 8008, 'message' => '激活神像',),
        'league_battle_worship' => array('code' => 8009, 'message' => '参拜神像',),
        'league_eat' => array('code' => 8010, 'message' => '公会食堂',),
        'league_quest_win' => array('code' => 8011, 'message' => '公会任务完成',),
        'league_recommend' => array('code' => 8012, 'message' => '推荐公会',),
        'league_quest_elite_bonus' => array('code' => 8013, 'message' => '公会精英任务奖励',),
        'league_battle_instance_bonus' => array('code' => 8014, 'message' => '失灭之战据点占领奖励',),
        'league_battle_win_bonus' => array('code' => 8015, 'message' => '失灭之战胜利奖励',),
        'league_battle_advance_bonus' => array('code' => 8016, 'message' => '失灭之战公会进度奖励',),
        'league_upgrade' => array('code' => 8017, 'message' => '升级公会建筑等级',),
        'league_appoint' => array('code' => 8018, 'message' => '公会职位',),

        //公会副本
        'league_boss_call' => array('code' => 8101, 'message' => '公会BOSS召唤',),
        'league_boss_call_force' => array('code' => 8102, 'message' => '公会BOSS强制召唤',),
        'league_boss_end' => array('code' => 8103, 'message' => '公会BOSS战斗结束',),
        'league_boss_rank' => array('code' => 8104, 'message' => '公会BOSS排名奖励',),
        'league_boss_buff' => array('code' => 8105, 'message' => '购买公会BOSS单体BUFF',),
        'league_boss_buff_all' => array('code' => 8106, 'message' => '购买公会BOSS全体BUFF',),

        //副本
        'instance_win' => array('code' => 9001, 'message' => '副本胜利',),
        'instance_lose' => array('code' => 9002, 'message' => '副本失败',),
        'instance_sweep' => array('code' => 9003, 'message' => '副本扫荡',),
        'instance_reset' => array('code' => 9004, 'message' => '副本重置',),
        'instance_receive_map_bonus' => array('code' => 9005, 'message' => '领取副本星数奖励',),

        //活动
        'novice_login_receive' => array('code' => 10001, 'message' => '新手登录奖励',),
        'daily_register_receive' => array('code' => 10002, 'message' => '每日签到奖励',),
        'daily_register_receive_now' => array('code' => 10003, 'message' => '付费领取每日签到奖励',),
        'vality_grant_receive' => array('code' => 10004, 'message' => '体力发放奖励',),
        'miracle_lake_drop' => array('code' => 10005, 'message' => '奇迹之湖献祭奖励',),
        'arena_fight' => array('code' => 10006, 'message' => '开始竞技场战斗',),
        'arena_win' => array('code' => 10007, 'message' => '竞技场胜利',),
        'arena_lose' => array('code' => 10008, 'message' => '竞技场失败',),
        'arena_buy_challenges' => array('code' => 10009, 'message' => '购买竞技场挑战次数',),
        'babel_refresh_now' => array('code' => 10010, 'message' => '付费重置通天塔',),
        'babel_win' => array('code' => 10011, 'message' => '通天塔胜利奖励',),
        'babel_reward' => array('code' => 10012, 'message' => '领取通天塔BOSS奖励',),
        'god_battle_win' => array('code' => 10013, 'message' => '神之试炼通关',),
        'god_battle_lose' => array('code' => 10014, 'message' => '神之试炼战败',),
        'abyss_battle_end' => array('code' => 10015, 'message' => '攻打世界BOSS',),
        'abyss_battle_fight_now' => array('code' => 10016, 'message' => '消除世界BOSS的CD时间',),
        'arena_rank_bonus' => array('code' => 10017, 'message' => '竞技场排名奖励',),
        'abyss_battle_rank_bonus' => array('code' => 10021, 'message' => '深渊之战排名奖励',),
        'pray_free' => array('code' => 10022, 'message' => '免费祈愿',),
        'pray_pay' => array('code' => 10023, 'message' => '付费祈愿',),
        'life_death_win' => array('code' => 10024, 'message' => '生死门战斗胜利',),
        'life_death_clear' => array('code' => 10025, 'message' => '生死门战斗通关',),
        'life_death_give_up' => array('code' => 10026, 'message' => '生死门放弃通关',),
        'life_death_buy' => array('code' => 10027, 'message' => '生死门购买奖励',),
        'exchange_code' => array('code' => 10028, 'message' => '使用兑换码',),
        'league_fight_rank' => array('code' => 10029, 'message' => '公会战排名结算',),
        'league_fight_buy_assault' => array('code' => 10030, 'message' => '购买公会战突击次数',),
        'league_fight_buy_challenges' => array('code' => 10031, 'message' => '购买公会战挑战次数',),
        'league_fight_start' => array('code' => 10032, 'message' => '开始一场公会战',),
        'league_fight_end' => array('code' => 10033, 'message' => '完成一场公会战',),
        'abyss_battle_clear' => array('code' => 10034, 'message' => '清除BOSS战冷却时间',),
        'combo_rank_bonus' => array('code' => 10035, 'message' => '每日连击榜奖励',),
        'online_bonus_receive' => array('code' => 10036, 'message' => '领取在线奖励',),
        'lucky_cat_end' => array('code' => 10037, 'message' => '完成猫男爵副本',),
        'lucky_cat_buy' => array('code' => 10038, 'message' => '购买猫男爵挑战次数',),
        'god_battle_buy' => array('code' => 10039, 'message' => '购买神之试炼挑战次数',),
        'babel_sweep_complete' => array('code' => 10040, 'message' => '完成通天塔扫荡',),
        'babel_sweep_complete_now' => array('code' => 10041, 'message' => '付费完成通天塔扫荡',),
        'fate_round' => array('code' => 10042, 'message' => '转动命运之轮',),
        'login_continuous_receive' => array('code' => 10043, 'message' => '领取连续登录奖励',),
        'new_server_bonus_receive' => array('code' => 10044, 'message' => '领取新服红包',),
        'life_death_battle_guard' => array('code' => 10045, 'message' => '审判之门守卫者奖励',),
        'pray_timed_free' => array('code' => 10046, 'message' => '免费限时祈愿',),
        'pray_timed_pay' => array('code' => 10047, 'message' => '付费限时祈愿',),
        'pray_timed_rank' => array('code' => 10048, 'message' => '限时祈愿排名奖励',),
        'league_arena_win' => array('code' => 10049, 'message' => 'PVP公会战胜利',),
        'league_arena_lose' => array('code' => 10050, 'message' => 'PVP公会战失败',),
        'league_arena_top' => array('code' => 10051, 'message' => 'PVP公会战区域榜首奖励',),
    ),

);

