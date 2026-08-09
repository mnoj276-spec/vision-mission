<?php

namespace App\Domains\Scrapers\Enums;

enum NotificationType: string
{
    case RECRUITMENT = 'recruitment';
    case RESULT = 'result';
    case ADMIT_CARD = 'admit_card';
    case ANSWER_KEY = 'answer_key';
    case FINAL_ANSWER_KEY = 'final_answer_key';
    case OBJECTION = 'objection_window';
    case INTERVIEW = 'interview_schedule';
    case DV = 'document_verification';
    case MEDICAL = 'medical_exam';
    case MERIT_LIST = 'merit_list';
    case COUNSELLING = 'counselling';
    case SEAT_ALLOTMENT = 'seat_allotment';
    case JOINING = 'joining_info';
    case TENDER_RECRUITMENT = 'tender_recruitment';
    case GUEST_FACULTY = 'guest_faculty';
    case INTERNSHIP = 'internship';
    case APPRENTICESHIP = 'apprenticeship';
    case WALK_IN = 'walk_in_interview';
    case SPORTS_QUOTA = 'sports_quota';
    case PWD = 'pwd_special_entry';
    case SC = 'sc_special_entry';
    case ST = 'st_special_entry';
    case OBC = 'obc_special_entry';
    case EWS = 'ews_special_entry';
    case EX_SERVICEMEN = 'ex_servicemen_entry';
    case PROMOTION = 'departmental_promotion';
    case TRANSFER = 'departmental_transfer';
    case CONTRACT = 'contract_recruitment';
    case RETIREMENT_VACANCY = 'retirement_vacancy';
    case SYLLABUS = 'syllabus';
    case SCHOLARSHIP = 'scholarship';
    case ADMISSION = 'admission';
    case EXAM_NOTICE = 'exam_notice';
    case UNKNOWN = 'unknown';

    /**
     * Map granular taxonomy to legacy post_type string values.
     */
    public function getBaseType(): string
    {
        return match ($this) {
            self::RECRUITMENT,
            self::GUEST_FACULTY,
            self::INTERNSHIP,
            self::APPRENTICESHIP,
            self::WALK_IN,
            self::SPORTS_QUOTA,
            self::PWD,
            self::SC,
            self::ST,
            self::OBC,
            self::EWS,
            self::EX_SERVICEMEN,
            self::CONTRACT,
            self::RETIREMENT_VACANCY => 'job',

            self::RESULT,
            self::MERIT_LIST => 'result',

            self::ADMIT_CARD => 'admit_card',

            self::ANSWER_KEY,
            self::FINAL_ANSWER_KEY,
            self::OBJECTION => 'answer_key',

            self::COUNSELLING,
            self::SEAT_ALLOTMENT,
            self::ADMISSION => 'admission',

            self::SYLLABUS => 'syllabus',

            self::SCHOLARSHIP => 'scholarship',

            self::INTERVIEW,
            self::DV,
            self::MEDICAL,
            self::JOINING,
            self::TENDER_RECRUITMENT,
            self::PROMOTION,
            self::TRANSFER,
            self::EXAM_NOTICE => 'notice',

            default => 'job',
        };
    }
}
