CREATE TABLE IF NOT EXISTS `form_lt_nutrition_screening_assessment` (
  `id`                        bigint(20)   NOT NULL AUTO_INCREMENT,
  `date`                      datetime     DEFAULT NULL,
  `pid`                       bigint(20)   DEFAULT 0,
  `user`                      varchar(255) DEFAULT NULL,
  `groupname`                 varchar(255) DEFAULT NULL,
  `authorized`                tinyint(4)   DEFAULT 0,
  `activity`                  tinyint(4)   DEFAULT 0,
  -- Demographics / social
  `employment_status`         varchar(50)  DEFAULT NULL,
  `living_arrangement`        varchar(50)  DEFAULT NULL,
  `meal_assistance`           varchar(50)  DEFAULT NULL,
  -- Appetite & intake
  `appetite`                  varchar(50)  DEFAULT NULL,
  `food_intake`               varchar(50)  DEFAULT NULL,
  `skip_meals`                varchar(50)  DEFAULT NULL,
  -- Weight
  `unintended_weight_change`  varchar(50)  DEFAULT NULL,
  `weight_change_direction`   varchar(50)  DEFAULT NULL,
  `weight_change_amount`      varchar(50)  DEFAULT NULL,
  -- Clinical nutrition risk
  `difficulty_chewing`        varchar(50)  DEFAULT NULL,
  `difficulty_swallowing`     varchar(50)  DEFAULT NULL,
  `nausea_vomiting`           varchar(50)  DEFAULT NULL,
  `diarrhea_constipation`     varchar(50)  DEFAULT NULL,
  `special_diet`              varchar(50)  DEFAULT NULL,
  `special_diet_type`         varchar(255) DEFAULT NULL,
  `food_allergies`            varchar(50)  DEFAULT NULL,
  `food_allergies_detail`     text         DEFAULT NULL,
  -- Functional / screening score
  `needs_assistance_eating`   varchar(50)  DEFAULT NULL,
  `nutrition_risk`            varchar(50)  DEFAULT NULL,
  -- Notes
  `additional_notes`          text         DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
