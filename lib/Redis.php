<?php
class Redis {
  private static $obj;

  static function &instance($db=1) {
    if (!isset(self::$obj[$db])) {
      self::$obj[$db] = new Redis();
      if (self::$obj[$db]->connect(REDIS_HOST, REDIS_PORT)) {
        self::$obj[$db]->select($db);
      }
    }
    return self::$obj[$db];
  }
}
