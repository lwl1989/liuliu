<?php
/**
 * Created by inke.
 * User: liwenlong@inke.cn
 * Date: 2020/7/14
 * Time: 22:03
 */

namespace App\Models\RegisterUsers;

use App\Models\Model;

/**
 * Class UserNotice
 * @package App\Models\RegisterUsers
 * @author  author  李文龙 <liwenlong@inke.cn>
 *   create table user_notice(
 * id int primary key auto_increment,
 * user_id int not null,
 * op_user_id int not null,
 * typ tinyint(1) not null,
 * obj_id int not null,
 * status tinyint(1) not null,
 * create_time timestamp default current_timestamp,
 * update_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
 * );
 */
class UserNotice extends Model
{
    public $table = 'user_notice';


    public $timestamps = true;
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';


}