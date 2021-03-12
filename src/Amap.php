<?php
namespace Pctco\Map;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use think\facade\Cache;
class Amap{
    /**
    * @name send
    * @describe send email
    * @param  string $to        接收人邮件地址
    * @param  string $subject     邮件标题
    * @param  string $contents  邮件内容 支持HTML格式
    * @return Boolean
    **/
    public static function send($options){
      set_time_limit(60);
      $find = Db::name('library_cn_provinces')
      ->where('up',0)
      ->field('id,name')
      ->find();
      $api = Smb::getCookie('http://restapi.amap.com/v3/config/district?subdistrict=4&key=778e8bd7e977163d8b3ded18de20099c&s=rsv3&output=json&keywords='.$find['name'],'amap.com');
      $arr = json_decode($api,true);
      if (!empty($arr['districts'][0]['districts'])) {
         $array = [];
         foreach ($arr['districts'][0]['districts'] as $key => $value) {
            $count = Db::name('library_cn_provinces')
            ->where([
            'adcode'=> $value['adcode'],
            'level'=>  $value['level']
            ])
            ->count();
            if (empty($count)) {
               $value['pid'] = $find['id'];
               unset($value['districts']);
               $array[] = $value;
            }
         }
         if (!empty($array)) {
            Db::name('library_cn_provinces')->insertAll($array);
            return 'The region has been insertAll id('.$find['id'].') successfully';
         }
      }
      Db::name('library_cn_provinces')->where('id',$find['id'])->setField('up',1);

      $arr = Db::name('library_cn_provinces')
      ->where(['pid'=>$find['id']])->select();
      if (!empty($arr)) {
         if ($event) {
            Db::name('library_cn_provinces')->where('id',$find['id'])->setField('sub',1);
            return 'The region has been updata id('.$find['id'].') successfully sub';
         }else{
            $dir = dirname(getcwd()).'/static/json/provinces/ZH-CN';
            if (!file_exists($dir)){
               mkdir ($dir,0777,true);
            }
            file_put_contents($dir.'/'.$find['id'].'.json',json_encode($arr));
            return 'The region has been updata id('.$find['id'].') successfully save json';
         }
      }
      return 'No new data to update';
   }
}
