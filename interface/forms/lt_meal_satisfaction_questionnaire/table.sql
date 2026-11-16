CREATE TABLE IF NOT EXISTS `form_lt_meal_satisfaction_questionnaire` (
  `id`                       bigint(20)   NOT NULL AUTO_INCREMENT,
  `date`                     datetime     DEFAULT NULL,
  `pid`                      bigint(20)   DEFAULT 0,
  `user`                     varchar(255) DEFAULT NULL,
  `groupname`                varchar(255) DEFAULT NULL,
  `authorized`               tinyint(4)   DEFAULT 0,
  `activity`                 tinyint(4)   DEFAULT 0,
  -- Section A: Appetite & Intake
  `appetite_change`          varchar(50)  DEFAULT NULL COMMENT 'Has appetite changed in last month?',
  `food_intake_change`       varchar(50)  DEFAULT NULL COMMENT 'Has food intake changed recently?',
  `intake_change_type`       varchar(50)  DEFAULT NULL COMMENT 'Type of intake change if any',
  -- Section B: Meal Satisfaction
  `meal_satisfaction`        varchar(50)  DEFAULT NULL COMMENT 'Overall meal satisfaction rating',
  `meal_variety`             varchar(50)  DEFAULT NULL COMMENT 'Satisfaction with meal variety',
  `meal_portion`             varchar(50)  DEFAULT NULL COMMENT 'Satisfaction with portion size',
  `meal_texture`             varchar(50)  DEFAULT NULL COMMENT 'Satisfaction with meal texture',
  -- Section C: Eating Difficulties
  `difficulty_chewing`       varchar(50)  DEFAULT NULL COMMENT 'Difficulty chewing',
  `difficulty_swallowing`    varchar(50)  DEFAULT NULL COMMENT 'Difficulty swallowing',
  `nausea_vomiting`          varchar(50)  DEFAULT NULL COMMENT 'Nausea or vomiting',
  `pain_eating`              varchar(50)  DEFAULT NULL COMMENT 'Pain when eating',
  -- Section D: Weight & Body
  `unintended_weight_loss`   varchar(50)  DEFAULT NULL COMMENT 'Unintended weight loss in last 6 months',
  `weight_loss_amount`       varchar(50)  DEFAULT NULL COMMENT 'Amount of weight lost',
  -- Notes
  `additional_notes`         text         DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
