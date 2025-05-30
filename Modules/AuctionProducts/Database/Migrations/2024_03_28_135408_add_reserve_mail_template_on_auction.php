<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReserveMailTemplateOnAuction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

       try{
            if (Schema::hasTable('email_templates')) {
                $types  = [
                    [
                        'type' => "reserve_failed",
                        'subject' => "Auction Bid not meet with Reserve Price",
                    ],

                    [
                        'type' => "auction_new_bid",
                        'subject' => "New Bid has been placed",
                    ],

                    [
                        'type' => "payment_reminder",
                        'subject' => "Payment Reminder for auction",
                    ],

                ];

                $tt  = [
                    [
                        'type' => "reserve_failed",
                    ],

                    [
                        'type' => "auction_new_bid",
                    ],

                    [
                        'type' => "payment_reminder",
                    ],

                ];
                DB::table('email_template_types')->insert($tt);
                foreach($types as $type)
                {
                    $tem_type = DB::table('email_template_types')->where('type',$type['type'])->first();
                    if($tem_type)
                    {

                        $template = [
                            "type_id" => $tem_type->id,
                            "subject" => isset($type['subject']) ? $type['subject']:'',
                            "value" => $this->template($type['subject']),
                            "is_active" => 1,
                            "reciepnt_type" => '["customer"]',
                        ];

                    DB::table('email_templates')->insert($template);
                    }
                }

        }
       }catch(Exception $e){
       }
    }

    public function template($message)
    {
        $temp = '<div style="font-family:&quot;text-align:center;background-color:#983e51;padding:30px;border-top-left-radius:3px;border-top-right-radius:3px;margin:0"><h1 style="margin:20px 0 10px;font-size:36px;font-family:&quot;line-height:1.1;color:inherit">Template</h1></div><div style="color:#000;font-family:&quot"><p style="color:#555">Hello {USER_FIRST_NAME}<br><br></p><hr style="box-sizing:content-box;margin-top:20px;margin-bottom:20px;border-top-color:#eee"><p style="color:#555">'.$message.'<br></p><p style="color:#555">{EMAIL_SIGNATURE}</p><p style="color:#555"><br></p></div><div style="font-family:&quot;text-align:center;background-color:#983e51;padding:30px;border-top-left-radius:3px;border-top-right-radius:3px;margin:0"><h1 style="margin:20px 0 10px;font-size:36px;font-family:&quot;line-height:1.1;color:inherit">Template</h1></div><div style="color:#000;font-family:&quot"></div>';
        return $temp;
    }
}
