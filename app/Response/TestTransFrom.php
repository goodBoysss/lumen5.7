<?php
namespace App\Response;

use League\Fractal\TransformerAbstract;

class TestTransFrom extends TransformerAbstract {

    public $admin = null;


    protected $availableIncludes = [];
    protected $defaultIncludes = [];

    public function __construct($admin)
    {
        $this->admin = $admin;
    }


    public function transform()
    {
        return [
            'admin_id' => $this->admin->admin_id,
            'admin_name' => $this->admin->admin_name,
        ];
    }
}



?>