<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190427152402 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE activity_level CHANGE sedentary sedentary INT DEFAULT NULL, CHANGE lightly lightly INT DEFAULT NULL, CHANGE fairly fairly INT DEFAULT NULL, CHANGE very very INT DEFAULT NULL');
        $this->addSql('ALTER TABLE api_access_log CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE entity entity VARCHAR(30) DEFAULT NULL, CHANGE last_retrieved last_retrieved DATETIME DEFAULT NULL, CHANGE last_pulled last_pulled DATETIME DEFAULT NULL, CHANGE cooldown cooldown DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE body_bmi CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE measurement measurement DOUBLE PRECISION DEFAULT NULL, CHANGE date_time date_time DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE body_fat CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE unit unit INT DEFAULT NULL, CHANGE part_of_day part_of_day INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE measurement measurement DOUBLE PRECISION DEFAULT NULL, CHANGE goal goal DOUBLE PRECISION DEFAULT NULL, CHANGE date_time date_time DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE body_weight CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE unit unit INT DEFAULT NULL, CHANGE part_of_day part_of_day INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE measurement measurement DOUBLE PRECISION DEFAULT NULL, CHANGE goal goal DOUBLE PRECISION DEFAULT NULL, CHANGE date_time date_time DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE count_daily_calories CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE date_time date_time DATE DEFAULT NULL, CHANGE value value INT DEFAULT NULL, CHANGE goal goal INT DEFAULT NULL');
        $this->addSql('ALTER TABLE count_daily_distance CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE unit unit INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE date_time date_time DATE DEFAULT NULL, CHANGE value value DOUBLE PRECISION DEFAULT NULL, CHANGE goal goal DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE count_daily_elevation CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE date_time date_time DATE DEFAULT NULL, CHANGE value value INT DEFAULT NULL');
        $this->addSql('ALTER TABLE count_daily_floor CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE date_time date_time DATETIME DEFAULT NULL, CHANGE value value INT DEFAULT NULL, CHANGE goal goal INT DEFAULT NULL');
        $this->addSql('ALTER TABLE count_daily_step CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE date_time date_time DATETIME DEFAULT NULL, CHANGE value value INT DEFAULT NULL, CHANGE goal goal INT DEFAULT NULL');
        $this->addSql('ALTER TABLE friends CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE remote_id remote_id VARCHAR(20) DEFAULT NULL, CHANGE name name VARCHAR(200) DEFAULT NULL, CHANGE avatar avatar VARCHAR(255) DEFAULT NULL, CHANGE gender gender VARCHAR(6) DEFAULT NULL, CHANGE rank rank INT DEFAULT NULL');
        $this->addSql('ALTER TABLE heart_rate CHANGE out_of_range_id out_of_range_id INT DEFAULT NULL, CHANGE fat_burn_id fat_burn_id INT DEFAULT NULL, CHANGE cardio_id cardio_id INT DEFAULT NULL, CHANGE peak_id peak_id INT DEFAULT NULL, CHANGE unit unit INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE average average INT DEFAULT NULL, CHANGE out_of_range_time out_of_range_time INT DEFAULT NULL, CHANGE fat_burn_time fat_burn_time INT DEFAULT NULL, CHANGE cardio_time cardio_time INT DEFAULT NULL, CHANGE peak_time peak_time INT DEFAULT NULL');
        $this->addSql('ALTER TABLE heart_rate_cardio CHANGE average average INT DEFAULT NULL, CHANGE min min INT DEFAULT NULL, CHANGE max max INT DEFAULT NULL');
        $this->addSql('ALTER TABLE heart_rate_fat_burn CHANGE average average INT DEFAULT NULL, CHANGE min min INT DEFAULT NULL, CHANGE max max INT DEFAULT NULL');
        $this->addSql('ALTER TABLE heart_rate_out_of_range CHANGE average average INT DEFAULT NULL, CHANGE min min INT DEFAULT NULL, CHANGE max max INT DEFAULT NULL');
        $this->addSql('ALTER TABLE heart_rate_peak CHANGE average average INT DEFAULT NULL, CHANGE min min INT DEFAULT NULL, CHANGE max max INT DEFAULT NULL');
        $this->addSql('ALTER TABLE heart_rate_resting CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE heart_rate_id heart_rate_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE date date DATE DEFAULT NULL, CHANGE value value INT DEFAULT NULL');
        $this->addSql('ALTER TABLE intraday_step CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE date date DATE DEFAULT NULL, CHANGE hour hour INT DEFAULT NULL, CHANGE value value INT DEFAULT NULL');
        $this->addSql('ALTER TABLE life_tracked CHANGE tracker tracker INT DEFAULT NULL, CHANGE date_time date_time DATETIME DEFAULT NULL, CHANGE lat lat VARCHAR(20) DEFAULT NULL, CHANGE lon lon VARCHAR(20) DEFAULT NULL, CHANGE value value INT DEFAULT NULL, CHANGE score score INT DEFAULT NULL');
        $this->addSql('ALTER TABLE life_tracker CHANGE service service INT DEFAULT NULL, CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE config config INT DEFAULT NULL, CHANGE remote_id remote_id VARCHAR(150) DEFAULT NULL, CHANGE name name VARCHAR(150) DEFAULT NULL, CHANGE icon icon VARCHAR(150) DEFAULT NULL, CHANGE colour colour VARCHAR(6) DEFAULT NULL, CHANGE charge charge INT DEFAULT NULL');
        $this->addSql('ALTER TABLE life_tracker_config CHANGE type type VARCHAR(255) DEFAULT NULL, CHANGE uom uom VARCHAR(255) DEFAULT NULL, CHANGE min min INT DEFAULT NULL, CHANGE max max INT DEFAULT NULL, CHANGE math math VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE life_tracker_score CHANGE life_tracker life_tracker INT DEFAULT NULL, CHANGE cond cond VARCHAR(255) DEFAULT NULL, CHANGE compare compare INT DEFAULT NULL, CHANGE charge charge INT DEFAULT NULL');
        $this->addSql('ALTER TABLE min_daily_fairly CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE date_time date_time DATE DEFAULT NULL, CHANGE value value INT DEFAULT NULL');
        $this->addSql('ALTER TABLE min_daily_lightly CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE date_time date_time DATE DEFAULT NULL, CHANGE value value INT DEFAULT NULL');
        $this->addSql('ALTER TABLE min_daily_sedentary CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE date_time date_time DATE DEFAULT NULL, CHANGE value value INT DEFAULT NULL');
        $this->addSql('ALTER TABLE min_daily_very CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE date_time date_time DATE DEFAULT NULL, CHANGE value value INT DEFAULT NULL, CHANGE goal goal INT DEFAULT NULL');
        $this->addSql('ALTER TABLE nutrition_information CHANGE unit unit INT DEFAULT NULL, CHANGE amount amount NUMERIC(10, 0) DEFAULT NULL, CHANGE brand brand VARCHAR(50) DEFAULT NULL, CHANGE name name VARCHAR(255) DEFAULT NULL, CHANGE meal meal VARCHAR(50) DEFAULT NULL, CHANGE calories calories INT DEFAULT NULL, CHANGE goal goal INT DEFAULT NULL, CHANGE carbs carbs INT DEFAULT NULL, CHANGE fat fat INT DEFAULT NULL, CHANGE fiber fiber INT DEFAULT NULL, CHANGE protein protein INT DEFAULT NULL, CHANGE sodium sodium INT DEFAULT NULL, CHANGE water water INT DEFAULT NULL, CHANGE goal_calories_out goal_calories_out INT DEFAULT NULL');
        $this->addSql('ALTER TABLE patient CHANGE fname fname VARCHAR(255) DEFAULT NULL, CHANGE lname lname VARCHAR(255) DEFAULT NULL, CHANGE birthday birthday DATE DEFAULT NULL, CHANGE height height DOUBLE PRECISION DEFAULT NULL, CHANGE email email VARCHAR(255) DEFAULT NULL, CHANGE gender gender VARCHAR(6) DEFAULT NULL, CHANGE password password VARCHAR(255) DEFAULT NULL, CHANGE timezone timezone VARCHAR(255) DEFAULT NULL, CHANGE avatar avatar VARCHAR(255) DEFAULT NULL, CHANGE step_goal step_goal INT DEFAULT NULL');
        $this->addSql('ALTER TABLE personal_plan CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE goals goals VARCHAR(50) DEFAULT NULL, CHANGE intensity intensity VARCHAR(50) DEFAULT NULL, CHANGE estimated_date estimated_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE reward CHANGE service service INT DEFAULT NULL, CHANGE remote_id remote_id VARCHAR(50) DEFAULT NULL, CHANGE name name VARCHAR(255) DEFAULT NULL, CHANGE value value INT DEFAULT NULL, CHANGE type type VARCHAR(50) DEFAULT NULL, CHANGE category category VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL, CHANGE gradient_end_color gradient_end_color VARCHAR(6) DEFAULT NULL, CHANGE gradient_start_color gradient_start_color VARCHAR(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE rewards_earned CHANGE reward reward INT DEFAULT NULL, CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE part_of_day part_of_day INT DEFAULT NULL, CHANGE date date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE sleep_episode CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE unit unit INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE remote_id remote_id BIGINT DEFAULT NULL, CHANGE start_time start_time DATETIME DEFAULT NULL, CHANGE end_time end_time DATETIME DEFAULT NULL, CHANGE latency_to_sleep_onset latency_to_sleep_onset INT DEFAULT NULL, CHANGE latency_to_arising latency_to_arising INT DEFAULT NULL, CHANGE total_sleep_time total_sleep_time INT DEFAULT NULL, CHANGE number_of_awakenings number_of_awakenings INT DEFAULT NULL, CHANGE is_main_sleep is_main_sleep TINYINT(1) DEFAULT NULL, CHANGE efficiency_percentage efficiency_percentage INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sleep_levels CHANGE sleep_episode sleep_episode INT DEFAULT NULL, CHANGE unit unit INT DEFAULT NULL, CHANGE date_time date_time DATETIME DEFAULT NULL, CHANGE level level VARCHAR(10) DEFAULT NULL, CHANGE value value INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sport_activity CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE source source INT DEFAULT NULL, CHANGE heart_rate_id heart_rate_id INT DEFAULT NULL, CHANGE activity_level_id activity_level_id INT DEFAULT NULL, CHANGE part_of_day part_of_day INT DEFAULT NULL, CHANGE sport_track sport_track INT DEFAULT NULL, CHANGE remote_id remote_id BIGINT DEFAULT NULL, CHANGE activity_name activity_name VARCHAR(255) DEFAULT NULL, CHANGE start_time start_time DATETIME DEFAULT NULL, CHANGE duration duration INT DEFAULT NULL, CHANGE steps steps INT DEFAULT NULL, CHANGE calories calories INT DEFAULT NULL, CHANGE elevation_gain elevation_gain DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE sport_track CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE tracking_device tracking_device INT DEFAULT NULL, CHANGE start_time start_time DATETIME DEFAULT NULL, CHANGE total_time total_time DOUBLE PRECISION DEFAULT NULL, CHANGE total_distance total_distance DOUBLE PRECISION DEFAULT NULL, CHANGE calories calories INT DEFAULT NULL, CHANGE intensity intensity VARCHAR(255) DEFAULT NULL, CHANGE method method VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sport_track_point CHANGE sport_track_id sport_track_id INT DEFAULT NULL, CHANGE time time DATETIME DEFAULT NULL, CHANGE lat lat VARCHAR(20) DEFAULT NULL, CHANGE lon lon VARCHAR(20) DEFAULT NULL, CHANGE altitude altitude VARCHAR(18) DEFAULT NULL, CHANGE distrance distrance DOUBLE PRECISION DEFAULT NULL, CHANGE heart_rate heart_rate INT DEFAULT NULL');
        $this->addSql('ALTER TABLE third_party_relations CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE username username VARCHAR(30) DEFAULT NULL, CHANGE member_since member_since DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE tracking_device CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE name name VARCHAR(150) DEFAULT NULL, CHANGE battery battery INT DEFAULT NULL, CHANGE last_sync_time last_sync_time DATETIME DEFAULT NULL, CHANGE remote_id remote_id VARCHAR(20) DEFAULT NULL, CHANGE type type VARCHAR(150) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE activity_level CHANGE sedentary sedentary INT DEFAULT NULL, CHANGE lightly lightly INT DEFAULT NULL, CHANGE fairly fairly INT DEFAULT NULL, CHANGE very very INT DEFAULT NULL');
        $this->addSql('ALTER TABLE api_access_log CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE entity entity VARCHAR(30) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE last_retrieved last_retrieved DATETIME DEFAULT \'NULL\', CHANGE last_pulled last_pulled DATETIME DEFAULT \'NULL\', CHANGE cooldown cooldown DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE body_bmi CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE measurement measurement DOUBLE PRECISION DEFAULT \'NULL\', CHANGE date_time date_time DATE DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE body_fat CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE unit unit INT DEFAULT NULL, CHANGE part_of_day part_of_day INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE measurement measurement DOUBLE PRECISION DEFAULT \'NULL\', CHANGE goal goal DOUBLE PRECISION DEFAULT \'NULL\', CHANGE date_time date_time DATE DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE body_weight CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE unit unit INT DEFAULT NULL, CHANGE part_of_day part_of_day INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE measurement measurement DOUBLE PRECISION DEFAULT \'NULL\', CHANGE goal goal DOUBLE PRECISION DEFAULT \'NULL\', CHANGE date_time date_time DATE DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE count_daily_calories CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE date_time date_time DATE DEFAULT \'NULL\', CHANGE value value INT DEFAULT NULL, CHANGE goal goal INT DEFAULT NULL');
        $this->addSql('ALTER TABLE count_daily_distance CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE unit unit INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE date_time date_time DATE DEFAULT \'NULL\', CHANGE value value DOUBLE PRECISION DEFAULT \'NULL\', CHANGE goal goal DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE count_daily_elevation CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE date_time date_time DATE DEFAULT \'NULL\', CHANGE value value INT DEFAULT NULL');
        $this->addSql('ALTER TABLE count_daily_floor CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE date_time date_time DATE DEFAULT \'NULL\', CHANGE value value INT DEFAULT NULL, CHANGE goal goal INT DEFAULT NULL');
        $this->addSql('ALTER TABLE count_daily_step CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE date_time date_time DATE DEFAULT \'NULL\', CHANGE value value INT DEFAULT NULL, CHANGE goal goal INT DEFAULT NULL');
        $this->addSql('ALTER TABLE friends CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE remote_id remote_id VARCHAR(20) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE name name VARCHAR(200) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE avatar avatar VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE gender gender VARCHAR(6) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE rank rank INT DEFAULT NULL');
        $this->addSql('ALTER TABLE heart_rate CHANGE service service INT DEFAULT NULL, CHANGE out_of_range_id out_of_range_id INT DEFAULT NULL, CHANGE fat_burn_id fat_burn_id INT DEFAULT NULL, CHANGE cardio_id cardio_id INT DEFAULT NULL, CHANGE peak_id peak_id INT DEFAULT NULL, CHANGE unit unit INT DEFAULT NULL, CHANGE average average INT DEFAULT NULL, CHANGE out_of_range_time out_of_range_time INT DEFAULT NULL, CHANGE fat_burn_time fat_burn_time INT DEFAULT NULL, CHANGE cardio_time cardio_time INT DEFAULT NULL, CHANGE peak_time peak_time INT DEFAULT NULL');
        $this->addSql('ALTER TABLE heart_rate_cardio CHANGE average average INT DEFAULT NULL, CHANGE min min INT DEFAULT NULL, CHANGE max max INT DEFAULT NULL');
        $this->addSql('ALTER TABLE heart_rate_fat_burn CHANGE average average INT DEFAULT NULL, CHANGE min min INT DEFAULT NULL, CHANGE max max INT DEFAULT NULL');
        $this->addSql('ALTER TABLE heart_rate_out_of_range CHANGE average average INT DEFAULT NULL, CHANGE min min INT DEFAULT NULL, CHANGE max max INT DEFAULT NULL');
        $this->addSql('ALTER TABLE heart_rate_peak CHANGE average average INT DEFAULT NULL, CHANGE min min INT DEFAULT NULL, CHANGE max max INT DEFAULT NULL');
        $this->addSql('ALTER TABLE heart_rate_resting CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE heart_rate_id heart_rate_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE date date DATE DEFAULT \'NULL\', CHANGE value value INT DEFAULT NULL');
        $this->addSql('ALTER TABLE intraday_step CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE date date DATE DEFAULT \'NULL\', CHANGE hour hour INT DEFAULT NULL, CHANGE value value INT DEFAULT NULL');
        $this->addSql('ALTER TABLE life_tracked CHANGE tracker tracker INT DEFAULT NULL, CHANGE date_time date_time DATETIME DEFAULT \'NULL\', CHANGE lat lat VARCHAR(20) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE lon lon VARCHAR(20) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE value value INT DEFAULT NULL, CHANGE score score INT DEFAULT NULL');
        $this->addSql('ALTER TABLE life_tracker CHANGE service service INT DEFAULT NULL, CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE config config INT DEFAULT NULL, CHANGE remote_id remote_id VARCHAR(150) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE name name VARCHAR(150) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE icon icon VARCHAR(150) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE charge charge INT DEFAULT NULL, CHANGE colour colour VARCHAR(6) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE life_tracker_config CHANGE type type VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE uom uom VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE min min INT DEFAULT NULL, CHANGE max max INT DEFAULT NULL, CHANGE math math VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE life_tracker_score CHANGE life_tracker life_tracker INT DEFAULT NULL, CHANGE cond cond VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE compare compare INT DEFAULT NULL, CHANGE charge charge INT DEFAULT NULL');
        $this->addSql('ALTER TABLE min_daily_fairly CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE date_time date_time DATE DEFAULT \'NULL\', CHANGE value value INT DEFAULT NULL');
        $this->addSql('ALTER TABLE min_daily_lightly CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE date_time date_time DATE DEFAULT \'NULL\', CHANGE value value INT DEFAULT NULL');
        $this->addSql('ALTER TABLE min_daily_sedentary CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE date_time date_time DATE DEFAULT \'NULL\', CHANGE value value INT DEFAULT NULL');
        $this->addSql('ALTER TABLE min_daily_very CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE date_time date_time DATE DEFAULT \'NULL\', CHANGE value value INT DEFAULT NULL, CHANGE goal goal INT DEFAULT NULL');
        $this->addSql('ALTER TABLE nutrition_information CHANGE unit unit INT DEFAULT NULL, CHANGE amount amount NUMERIC(10, 0) DEFAULT \'NULL\', CHANGE brand brand VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE name name VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE meal meal VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE calories calories INT DEFAULT NULL, CHANGE goal goal INT DEFAULT NULL, CHANGE carbs carbs INT DEFAULT NULL, CHANGE fat fat INT DEFAULT NULL, CHANGE fiber fiber INT DEFAULT NULL, CHANGE protein protein INT DEFAULT NULL, CHANGE sodium sodium INT DEFAULT NULL, CHANGE water water INT DEFAULT NULL, CHANGE goal_calories_out goal_calories_out INT DEFAULT NULL');
        $this->addSql('ALTER TABLE patient CHANGE fname fname VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE lname lname VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE birthday birthday DATE DEFAULT \'NULL\', CHANGE height height DOUBLE PRECISION DEFAULT \'NULL\', CHANGE email email VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE gender gender VARCHAR(6) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE timezone timezone VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE avatar avatar VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE password password VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE step_goal step_goal INT DEFAULT NULL');
        $this->addSql('ALTER TABLE personal_plan CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE goals goals VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE intensity intensity VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE estimated_date estimated_date DATE DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE reward CHANGE service service INT DEFAULT NULL, CHANGE remote_id remote_id VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE name name VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE value value INT DEFAULT NULL, CHANGE type type VARCHAR(50) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE category category VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE description description VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE image image VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE gradient_end_color gradient_end_color VARCHAR(6) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE gradient_start_color gradient_start_color VARCHAR(6) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE rewards_earned CHANGE reward reward INT DEFAULT NULL, CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE part_of_day part_of_day INT DEFAULT NULL, CHANGE date date DATE DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE sleep_episode CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE unit unit INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE remote_id remote_id BIGINT DEFAULT NULL, CHANGE start_time start_time DATETIME DEFAULT \'NULL\', CHANGE end_time end_time DATETIME DEFAULT \'NULL\', CHANGE latency_to_sleep_onset latency_to_sleep_onset INT DEFAULT NULL, CHANGE latency_to_arising latency_to_arising INT DEFAULT NULL, CHANGE total_sleep_time total_sleep_time INT DEFAULT NULL, CHANGE number_of_awakenings number_of_awakenings INT DEFAULT NULL, CHANGE is_main_sleep is_main_sleep TINYINT(1) DEFAULT \'NULL\', CHANGE efficiency_percentage efficiency_percentage INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sleep_levels CHANGE sleep_episode sleep_episode INT DEFAULT NULL, CHANGE unit unit INT DEFAULT NULL, CHANGE date_time date_time DATETIME DEFAULT \'NULL\', CHANGE level level VARCHAR(10) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE value value INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sport_activity CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE source source INT DEFAULT NULL, CHANGE heart_rate_id heart_rate_id INT DEFAULT NULL, CHANGE activity_level_id activity_level_id INT DEFAULT NULL, CHANGE part_of_day part_of_day INT DEFAULT NULL, CHANGE sport_track sport_track INT DEFAULT NULL, CHANGE remote_id remote_id BIGINT DEFAULT NULL, CHANGE activity_name activity_name VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE start_time start_time DATETIME DEFAULT \'NULL\', CHANGE duration duration INT DEFAULT NULL, CHANGE steps steps INT DEFAULT NULL, CHANGE calories calories INT DEFAULT NULL, CHANGE elevation_gain elevation_gain DOUBLE PRECISION DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE sport_track CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE tracking_device tracking_device INT DEFAULT NULL, CHANGE start_time start_time DATETIME DEFAULT \'NULL\', CHANGE total_time total_time DOUBLE PRECISION DEFAULT \'NULL\', CHANGE total_distance total_distance DOUBLE PRECISION DEFAULT \'NULL\', CHANGE calories calories INT DEFAULT NULL, CHANGE intensity intensity VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE method method VARCHAR(255) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE sport_track_point CHANGE sport_track_id sport_track_id INT DEFAULT NULL, CHANGE time time DATETIME DEFAULT \'NULL\', CHANGE lat lat VARCHAR(20) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE lon lon VARCHAR(20) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE altitude altitude VARCHAR(18) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE distrance distrance DOUBLE PRECISION DEFAULT \'NULL\', CHANGE heart_rate heart_rate INT DEFAULT NULL');
        $this->addSql('ALTER TABLE third_party_relations CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE username username VARCHAR(30) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE member_since member_since DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE tracking_device CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE service service INT DEFAULT NULL, CHANGE name name VARCHAR(150) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE battery battery INT DEFAULT NULL, CHANGE last_sync_time last_sync_time DATETIME DEFAULT \'NULL\', CHANGE remote_id remote_id VARCHAR(20) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci, CHANGE type type VARCHAR(150) DEFAULT \'NULL\' COLLATE utf8mb4_unicode_ci');
    }
}
