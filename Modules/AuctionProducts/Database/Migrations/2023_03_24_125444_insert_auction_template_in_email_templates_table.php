<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InsertAuctionTemplateInEmailTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('email_templates')){
            DB::statement("INSERT INTO `email_templates` (`type_id`, `subject`, `value`, `is_active`, `relatable_type`, `relatable_id`, `reciepnt_type`, `created_at`, `updated_at`) VALUES
            ('44', 'Winner of Auction', '<div style=\"font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; color: rgb(255, 255, 255); text-align: center; background-color: rgb(152, 62, 81); padding: 30px; border-top-left-radius: 3px; border-top-right-radius: 3px; margin: 0px;\"><h1 style=\"margin: 20px 0px 10px; font-size: 36px; font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; font-weight: 500; line-height: 1.1; color: inherit;\">Template</h1></div><div style=\"color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; padding: 20px;\"><p style=\"color: rgb(85, 85, 85);\">Hello {USER_FIRST_NAME}<br><br>Auction has ended. You have been selected as Winner.</p><p style=\"color: rgb(85, 85, 85);\">Please use the following link to confirm your order:</p><p style=\"color: rgb(85, 85, 85);\">{VERIFICATION_LINK}<br></p><hr style=\"box-sizing: content-box; margin-top: 20px; margin-bottom: 20px; border-top-color: rgb(238, 238, 238);\"><p style=\"color: rgb(85, 85, 85);\"><br></p><p style=\"color: rgb(85, 85, 85);\">{EMAIL_SIGNATURE}</p><p style=\"color: rgb(85, 85, 85);\"><br></p></div>', 1, NULL, NULL, '[\"customer\"]', NULL, '2023-03-24 05:44:21'),
            ('45', 'Auction has Ended', '<div style=\"font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; color: rgb(255, 255, 255); text-align: center; background-color: rgb(152, 62, 81); padding: 30px; border-top-left-radius: 3px; border-top-right-radius: 3px; margin: 0px;\"><h1 style=\"margin: 20px 0px 10px; font-size: 36px; font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; font-weight: 500; line-height: 1.1; color: inherit;\">Template</h1></div><div style=\"color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; padding: 20px;\"><p style=\"color: rgb(85, 85, 85);\">Hello {USER_FIRST_NAME}<br><br>The Auction has ended. Please check and select top bidder to award or increase auction end time.</p><p style=\"color: rgb(85, 85, 85);\">Please use the following link to see the auction:</p><p style=\"color: rgb(85, 85, 85);\">{VERIFICATION_LINK}<br></p><hr style=\"box-sizing: content-box; margin-top: 20px; margin-bottom: 20px; border-top-color: rgb(238, 238, 238);\"><p style=\"color: rgb(85, 85, 85);\"><br></p><p style=\"color: rgb(85, 85, 85);\">{EMAIL_SIGNATURE}</p><p style=\"color: rgb(85, 85, 85);\"><br></p></div><div style=\"font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; color: rgb(255, 255, 255); text-align: center; background-color: rgb(152, 62, 81); padding: 30px; border-top-left-radius: 3px; border-top-right-radius: 3px; margin: 0px;\"><h1 style=\"margin: 20px 0px 10px; font-size: 36px; font-family: &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; font-weight: 500; line-height: 1.1; color: inherit;\">Template</h1></div><div style=\"color: rgb(0, 0, 0); font-family: &quot;Open Sans&quot;, &quot;Helvetica Neue&quot;, Helvetica, Arial, sans-serif; padding: 20px;\"></div>', 1, NULL, NULL, '[\"admin\",\"seller\"]', NULL, '2023-03-24 05:44:21')");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('email_templates');
    }
}
