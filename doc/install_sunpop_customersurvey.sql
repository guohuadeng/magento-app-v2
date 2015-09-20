--
-- Table structure for table `survey`
--

CREATE TABLE IF NOT EXISTS `survey` (
  `customersurvey_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `enabled` tinyint(4) NOT NULL,
  `code` text NOT NULL,
  `code_title` text NOT NULL,
  PRIMARY KEY (`customersurvey_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

--
-- Dumping data for table `survey`
--

INSERT INTO `survey` (`customersurvey_id`, `title`, `enabled`, `code`, `code_title`) VALUES
(1, 'General Website Survey', 1, '', ''),
(2, 'General Satisfaction', 1, '', '');

--
-- Table structure for table `survey_questions`
--

CREATE TABLE IF NOT EXISTS `survey_questions` (
  `question_id` int(11) NOT NULL AUTO_INCREMENT,
  `customersurvey_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `answer_type` text NOT NULL,
  `sort_order` int(11) NOT NULL,
  `possible_answers` text,
  PRIMARY KEY (`question_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=141 ;

--
-- Dumping data for table `survey_questions`
--

INSERT INTO `survey_questions` (`question_id`, `customersurvey_id`, `question`, `answer_type`, `sort_order`) VALUES
(15, 1, '我们的App用起来方便吗?', 'yesno', 5),
(14, 1, '您是第一次使用我们的产品吗?', 'yesno', 6),
(13, 1, '您怎么评价我们App的功能?', 'bubbles2', 3),
(12, 1, '请写下您的评论:', 'area', 7),
(11, 2, '请您对我们的印章产品进行评价.', 'bubbles2', 2),
(10, 2, '请您对我们的客户服务进行评价.', 'bubbles2', 2),
(8, 1, '我们的App外观如何?', 'bubbles2', 1),
(7, 2, '我们有哪些地方需要改进，请提出您的宝贵意见?', 'area', 4),
(6, 2, '附加评论:', 'area', 5),
(5, 2, '您对我们的产品售后服务满意不?', 'bubbles1', 1),
(4, 2, '未来您还有购买我们产品的计划吗?', 'yesno', 3),
(3, 2, '您对我们公司评价如何?', 'bubbles1', 0),
(2, 1, '我们的App菜单使用方便吗?', 'yesno', 4),
(1, 1, '您觉得我们App的产品内容如何?', 'bubbles2', 2);

--
-- Table structure for table `survey_results`
--

CREATE TABLE IF NOT EXISTS `survey_results` (
  `result_id` int(11) NOT NULL AUTO_INCREMENT,
  `customersurvey_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer` text NOT NULL,
  `customer_id` int(11) NOT NULL,
  `input_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `unique_id` int(11) NOT NULL,
  PRIMARY KEY (`result_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=174 ;