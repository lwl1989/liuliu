<?php

namespace App\Models;


use App\Library\Constant\Common;
use App\Models\Admin\AdminProfile;
use App\Models\Admin\AdminShopProfile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Query\Builder as QueryBuilder;


class Admin extends Authenticatable
{
        public $table = 'admin';

        //,'department_id','alias','tel','tel_ext','mobile','email','permissions'
        protected $fillable = ['name','account','password','role','permissions'];
        protected $hidden = ['password'];
        public $timestamps = false;

        use Notifiable;

        public function  newQuery() {
                $query = $this->newBaseQueryBuilder();
                $builder =  new class($query) extends Builder {

                        /**
                         * Create a new Eloquent query builder instance.
                         *
                         * @param  \Illuminate\Database\Query\Builder  $query
                         * @return void
                         */
                        public function __construct(QueryBuilder $query)
                        {

                                parent::__construct($query);
                        }
                        /**
                         * Execute the query and get the first result.
                         *
                         * @param  array  $columns
                         * @return \Illuminate\Database\Eloquent\Model|object|static|null
                         */
                        public function first($columns = ['*']) {

                                /** @var Model $user */
                                $user =  $this->take(1)->get($columns)->first();
                                //從別的模型查數據  設置到這個模型裏面
                                if($user!=null) {
                                        if($user->getAttributeValue('role') == Common::ADMIN_ROLE_SHOP) {
                                            $profile = AdminShopProfile::query()
                                                ->where('admin_id','=',$user->getAttributeValue('id'))->take(1)
                                                ->get()->first();
                                        }else{
                                            $profile = AdminProfile::query()
                                                ->where('admin_id','=',$user->getAttributeValue('id'))->take(1)
                                                ->get()->first();
                                        }

                                        if(!empty($profile)) {
                                            foreach ($profile as $key => $value) {
                                                if($key == 'id') {
                                                        $key = $user->getAttributeValue('role') == Common::ADMIN_ROLE_SHOP ? 'shop_id' : 'profile_id';
                                                }
                                                $user->setAttribute($key, $value);
                                            }
                                        }
                                }
                                return $user;
                        }

                };
                $builder->setModel($this);
                return $builder;
        }

        public function getAuthIdentifierName() {
                return 'id';
        }

        public function __get($name) {
                $value = $this->getAttributeValue($name);
                if(is_null($value)) {
                        return '';
                }
                if(!is_string($value)) {
                        return json_encode($value);
                }
                return $value;
        }

}