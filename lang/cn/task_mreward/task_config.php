<?php
/**
 * ��������
 * @version kppw2.0
 * @author deng
 * 2011-12-22
 */
$lang = array(
/*task_config.php*/
      'update_success'=>'�޸ĳɹ�',
      'update_fail'=>'�޸�ʧ��',
      'permissions_config_update_success'=>'Ȩ�������޸ĳɹ���',
      'has_update_more_reward'=>'�޸��˶�������ְλ��',

      
/*task_config.htm*/
      'industry_binding'=>'��ҵ��',
      'choose_industry'=>'ѡ����ҵ',
      'industry_binding_notice'=>'(��ҵ�󶨺��ְλģ��ֻ����ѡ�����ҵʱ��ʹ��,���Զ�ѡ.)',
      'model_about'=>'ģ�ͼ��',
      'limit_50_bytes'=>'��50�ֽ�',
      'model_description'=>'ģ������',
      'last_modify'=>'�ϴ��޸�ʱ��',
      'task_min_money_error'=>'ְλ��С����ȷ������2-5',
      'yuan_over'=>'Ԫ����',
      'continue'=>'����',
      'day_not_null'=>'��������Ϊ��! �����ĳ���1-2',
      'first_rule_not_delete'=>'��һ�������ܱ�ɾ����',
      'no_less_than_reward_total'=>'�����������ܽ���',
      'percent_not_null'=>'�ٷֱȲ���Ϊ�գ�',


/*task_control.htm*/
      'task_commission_strategy'=>'ְλӶ�����',
      'task_audit_money_set'=>'ְλ��˽���趨',
      'greater_money_not_audit_notice'=>'�����������ְλ����Ҫ��ˣ�������Ҫ����Ա���',
      'task_min_money_set'=>'ְλ��С����趨',
      'fill_in_right_audit_money'=>'��д��ȷְλ��˽��',
      'task_audit_money_allow_decimal'=>'ְλ��˽������С��',
      'task_min_money_input_error'=>'ְλ��С�����д����',
      'task_min_money_allow_decimal'=>'ְλ��С���Ϊ���Ժ�С��',
      'task_min_money_positive_integer'=>'ְλ����С���Ϊ������������',
      'task_royalty_rate'=>'ְλ��ɱ���',
      'task_royalty_rate_notice'=>'ְλ��ɱ���ֵΪ�����������ȣ�1-2',
      'task_royalty_rate_title'=>'ְλ��ɱ���ֵΪ������,0-100',
      'task_success_platform_rate'=>'ְλ�ɹ�ʱ��ƽ̨����ɱ���',
      'task_fail_rate'=>'ְλʧ����ɱ���',
      'fail_notice'=>'�����ʧ�ܣ�1�ǻ񽱸����Ϊ0��2�ǻ񽱸�����ȹ�������Ľ�����Сʱ���񽱵ĸ�����ɹ�������ɣ�ʣ��Ľ��ʧ�����',
      'task_royalty_rate_msg'=>'ְλ��ɱ���ֵΪ�����������ȣ�1-2',
      'task_royalty_rate_title'=>'ְλ��ɱ���ֵΪ������,0-100',
      'task_overdue_operate_set'=>'ְλ���ڲ����趨',
      'refund'=>'��Ǯ',
      'auto_choose'=>'�Զ�ѡ��',
      'return_cash'=>'�����ֽ�',
      'return_cash_coupon'=>'��������ȯ',
      'task_fail_process'=>'ְλʧ�ܴ���',
      'deduction_commission_money'=>'�۳�ӵ����Ǯ',
      'task_time_rule_set'=>'ְλʱ������趨',
      'time_rule'=>'ʱ�����',
      'task_min_money_error'=>'ְλ��С����ȷ',
      'please_carefully_input_min_money'=>'����ϸ��д����������С���',
      'day_must_greater_one'=>'��������Ϊ����1������',
      'please_carefully_input_day'=>'����ϸ��д��������������1��',
      'delete_rule'=>'ɾ������',
      'add_rule'=>'��ӹ���',
      'task_show_period_time'=>'ְλ��ʾ��ʱ��',
      'task_min_day'=>'ְλ��������',
      'show_period_time_error'=>'��ʾ��ʱ�䲻��',
      'show_time_min_one_day'=>'ְλ��ʾ����Сʱ��Ϊ1��',
      'min_one_day'=>'��СֵΪ1��',
      'task_min_time_error'=>'ְλ��Сʱ�䲻��,����һ��',
      'task_min_time_one_day'=>'ְλ��Сʱ��Ϊ1��',
      'task_vote_period_time'=>'ְλͶƱ��ʱ��',
      'vote_period_time_error'=>'ͶƱ��ʱ�䲻��,����',
      'vote_period_min_one_day'=>'ְλͶƱ����Сʱ��Ϊ1��',
      'task_no_final_to_vote'=>'ְλû�ж���ʱ��ͨ��ͶƱ��������������1��',
      'choose_time_set'=>'ѡ��ʱ������',
      'choose_time_input_error'=>'ѡ��ʱ����������',
      'choose_time_notice'=>'ְλѡ��ʱ������Ϊ1�죬���20��',
      'in_choose'=>'������ѡ��',
      'delay_rule_set'=>'���ڹ����趨',
      'delay_min_money'=>'������С���',
      'every_time_delay_money_error'=>'ÿ�����ڽ�����ٽ����д����',
      'task_delay_min_one_yuan'=>'ְλ�������ٽ��Ϊ1Ԫ',
      'delay_days_limit'=>'������������',
      'every_max_delay_days_error'=>'ÿ�����������������ȷ',
      'max_delay_days_notice'=>'ְλ���������������С��2��',
      'max_delay'=>'�������',
      'delay_set'=>'��������',
      'rate_input_error'=>'������д����',
      'delay_rate_one_to_hundred'=>'ְλ���ڱ���Ϊ0-100',
      'task_comment_set'=>'ְλ��������',
      'is_public'=>'�Ƿ񹫿�',
      'tick_comment_notice'=>'��ѡ��������ְλ���������أ�ְλ��������',

/*task_priv.htm*/
      'item_name'=>'��Ŀ����',     
      'user_identity'=>'�û����',
      'times_limit'=>'��������',


);