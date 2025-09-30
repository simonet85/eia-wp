<?php

namespace LearnPress\Filters;

use LearnPress\Databases\PostDB;
use LearnPress\Filters\PostFilter;
use LP_Filter;

defined( 'ABSPATH' ) || exit();

/**
 * Class OrderPostFilter
 *
 * Filter post type LP Order
 *
 * @version 1.0.0
 * @since 4.2.9.3
 */
class OrderItemmeta extends FilterBase {
	const COL_QUESTION_ANSWER_ID = 'question_answer_id';
	const COL_QUESTION_ID        = 'question_id';
	const COL_TITLE              = 'title';
	const COL_VALUE              = 'value';
	const COL_ORDER              = 'order';
	const COL_IS_TRUE            = 'is_true';
	/**
	 * @var string[]
	 */
	public $all_fields = [
		self::COL_QUESTION_ANSWER_ID,
		self::COL_QUESTION_ID,
		self::COL_TITLE,
		self::COL_VALUE,
		self::COL_ORDER,
		self::COL_IS_TRUE,
	];

	public $field_count = 'question_answer_id';
	/**
	 * @var int
	 */
	public $question_answer_id;
	/**
	 * @var int
	 */
	public $question_id;
	/**
	 * @var string
	 */
	public $title;
	/**
	 * @var string
	 */
	public $value;
	/**
	 * @var string
	 */
	public $is_true;
	/**
	 * @var int[]
	 */
	public $question_answer_ids = [];
	/**
	 * @var int[]
	 */
	public $question_ids = [];
	public $order_by     = '`order`';
	public $order        = 'ASC';
}
