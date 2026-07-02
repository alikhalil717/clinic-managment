# Dental Clinic Management System — UML Class Diagram

```mermaid
---
title: Dental Clinic Management System - UML Class Diagram
---
classDiagram

    %% ====================================================================
    %%  BASE / ABSTRACT CLASS
    %% ====================================================================
    class ClinicModel {
        <<abstract>>
        # $guarded : array
        # $timestamps : bool
        # $table : string
        # $primaryKey : string
        + static factory() : Factory
    }

    %% ====================================================================
    %%  AUTH / USER CLASSES
    %% ====================================================================
    class User {
        - user_id : int
        - first_name : string
        - last_name : string
        - email : string
        - phone : string
        - password : string
        - role : string
        - api_token : string
        + admin() : HasOne~Admin~
        + secretary() : HasOne~Secretary~
        + doctor() : HasOne~Doctor~
        + patient() : HasOne~Patient~
    }

    class Admin {
        - admin_id : int
        - permissions : string
        + user() : BelongsTo~User~
    }

    class Secretary {
        - secretary_id : int
        - shift : string
        - office_number : string
        + user() : BelongsTo~User~
    }

    class Doctor {
        - doctor_id : int
        - specialization : string
        - license_number : string
        - years_of_experience : int
        - rating : float
        - reviews_count : int
        + user() : BelongsTo~User~
        + appointments() : HasMany~Appointment~
        + treatmentPlans() : HasMany~TreatmentPlan~
        + treatmentSessions() : HasMany~TreatmentSession~
        + toothConditions() : HasMany~ToothCondition~
        + payouts() : HasMany~DoctorPayout~
        + ratings() : HasMany~Rating~
        + diagnoses() : HasMany~Diagnosis~
    }

    class Patient {
        - patient_id : int
        - date_of_birth : date
        \ age : int
        + user() : BelongsTo~User~
        + medicalRecords() : HasMany~MedicalRecord~
        + appointments() : HasMany~Appointment~
        + treatmentPlans() : HasMany~TreatmentPlan~
        + treatmentSessions() : HasMany~TreatmentSession~
        + toothConditions() : HasMany~ToothCondition~
        + payments() : HasMany~Payment~
        + ratings() : HasMany~Rating~
        + points() : HasMany~PatientPoints~
        + diagnoses() : HasMany~Diagnosis~
    }

    %% ====================================================================
    %%  APPOINTMENT & TREATMENT CLASSES
    %% ====================================================================
    class Appointment {
        - appointment_id : int
        - patient_id : int
        - doctor_id : int
        - date : date
        - start_time : time
        - end_time : time
        - status : string
        - notes : string
        + patient() : BelongsTo~Patient~
        + doctor() : BelongsTo~Doctor~
        + treatmentSessions() : HasMany~TreatmentSession~
    }

    class TreatmentSession {
        - session_id : int
        - appointment_id : int
        - doctor_id : int
        - patient_id : int
        - session_date : date
        - estimated_cost : float
        - notes : string
        + appointment() : BelongsTo~Appointment~
        + doctor() : BelongsTo~Doctor~
        + patient() : BelongsTo~Patient~
        + toothConditions() : HasMany~ToothCondition~
        + treatmentDetails() : HasMany~TreatmentDetails~
        + payments() : HasMany~Payment~
        + payouts() : HasMany~DoctorPayout~
        + diagnoses() : HasMany~Diagnosis~
    }

    class TreatmentPlan {
        - plan_id : int
        - patient_id : int
        - doctor_id : int
        - estimated_total_cost : float
        - actual_total_cost : float
        - progress_percentage : int
        - created_at : DateTime
        + patient() : BelongsTo~Patient~
        + doctor() : BelongsTo~Doctor~
        + stages() : HasMany~TreatmentStage~
    }

    class TreatmentStage {
        - stage_id : int
        - plan_id : int
        - stage_name : string
        - description : string
        - estimated_cost : float
        - actual_cost : float
        - status : string
        - start_date : date
        - end_date : date
    }

    %% ====================================================================
    %%  DENTAL / TOOTH CLASSES
    %% ====================================================================
    class Tooth {
        - tooth_id : int
        - tooth_code : string
        - tooth_name : string
    }

    class ToothCondition {
        - condition_id : int
        - patient_id : int
        - tooth_id : int
        - doctor_id : int
        - session_id : int
        - condition_status : string
        - treatment_type : string
        - treatment_description : string
        - estimated_price : float
        - severity_level : string
        - notes : string
        + patient() : BelongsTo~Patient~
        + tooth() : BelongsTo~Tooth~
        + doctor() : BelongsTo~Doctor~
        + session() : BelongsTo~TreatmentSession~
    }

    class TreatmentDetails {
        - detail_id : int
        - session_id : int
        - tooth_id : int
        - previous_condition : string
        - new_condition : string
        - cost : float
        + session() : BelongsTo~TreatmentSession~
        + tooth() : BelongsTo~Tooth~
    }

    %% ====================================================================
    %%  MEDICAL RECORDS
    %% ====================================================================
    class MedicalRecord {
        - record_id : int
        - patient_id : int
        - created_at : DateTime
        + patient() : BelongsTo~Patient~
        + histories() : HasMany~MedicalHistory~
        + allergies() : HasMany~Allergy~
        + diagnoses() : HasMany~Diagnosis~
    }

    class MedicalHistory {
        - history_id : int
        - record_id : int
        - condition_name : string
        - description : string
        - diagnosed_date : date
        + record() : BelongsTo~MedicalRecord~
    }

    class Allergy {
        - allergy_id : int
        - record_id : int
        - allergy_name : string
        - severity : string
        - notes : string
        + record() : BelongsTo~MedicalRecord~
    }

    class Diagnosis {
        - diagnosis_id : int
        - record_id : int
        - patient_id : int
        - doctor_id : int
        - session_id : int
        - diagnosis_name : string
        - description : string
        - severity : string
        - diagnosed_at : DateTime
        + record() : BelongsTo~MedicalRecord~
        + patient() : BelongsTo~Patient~
        + doctor() : BelongsTo~Doctor~
        + session() : BelongsTo~TreatmentSession~
    }

    %% ====================================================================
    %%  FINANCIAL CLASSES
    %% ====================================================================
    class Payment {
        - payment_id : int
        - patient_id : int
        - amount : float
        - method : string
        - date : DateTime
        - related_session_id : int
        - type : string
        - is_income : bool
        + patient() : BelongsTo~Patient~
        + relatedSession() : BelongsTo~TreatmentSession~
    }

    class DoctorPayout {
        - payout_id : int
        - doctor_id : int
        - session_id : int
        - amount : float
        - payout_date : DateTime
        - status : string
        - notes : string
        + doctor() : BelongsTo~Doctor~
        + session() : BelongsTo~TreatmentSession~
    }

    %% ====================================================================
    %%  ENGAGEMENT CLASSES
    %% ====================================================================
    class Rating {
        - rating_id : int
        - patient_id : int
        - doctor_id : int
        - rating : int
        - created_at : DateTime
        + patient() : BelongsTo~Patient~
        + doctor() : BelongsTo~Doctor~
    }

    class PatientPoints {
        - point_id : int
        - patient_id : int
        - points : int
        - source : string
        - related_id : int
        - description : string
        - created_at : DateTime
        + patient() : BelongsTo~Patient~
    }

    class Notification {
        - notification_id : int
        - user_id : int
        - title : string
        - message : string
        - type : string
        - related_id : int
        - is_read : bool
        - created_at : DateTime
        + user() : BelongsTo~User~
    }

    %% ====================================================================
    %%  API / CONTROLLER & SERVICE LAYER
    %% ====================================================================
    class DoctorAuthController {
        + register(DoctorRegisterRequest) : JsonResponse
        + login(DoctorLoginRequest) : JsonResponse
        + logout(Request) : JsonResponse
    }

    class PatientAuthController {
        + register(PatientRegisterRequest) : JsonResponse
        + login(PatientLoginRequest) : JsonResponse
        + logout(Request) : JsonResponse
    }

    class DoctorAuthService {
        + register(DoctorRegisterRequest) : JsonResponse
        + login(DoctorLoginRequest) : JsonResponse
        + logout(Request) : JsonResponse
    }

    class PatientAuthService {
        + register(PatientRegisterRequest) : JsonResponse
        + login(PatientLoginRequest) : JsonResponse
        + logout(Request) : JsonResponse
    }

    class DoctorRegisterRequest {
        + authorize() : bool
        + rules() : array
    }

    class PatientRegisterRequest {
        + authorize() : bool
        + rules() : array
    }

    class DoctorLoginRequest {
        + authorize() : bool
        + rules() : array
    }

    class PatientLoginRequest {
        + authorize() : bool
        + rules() : array
    }

    class EnsureBearerRole {
        + handle(Request, Closure, string...) : mixed
    }

    %% ====================================================================
    %%  INHERITANCE — ClinicModel is the abstract base
    %% ====================================================================
    ClinicModel <|-- Admin
    ClinicModel <|-- Secretary
    ClinicModel <|-- Doctor
    ClinicModel <|-- Patient
    ClinicModel <|-- Appointment
    ClinicModel <|-- TreatmentPlan
    ClinicModel <|-- TreatmentStage
    ClinicModel <|-- TreatmentSession
    ClinicModel <|-- Tooth
    ClinicModel <|-- ToothCondition
    ClinicModel <|-- TreatmentDetails
    ClinicModel <|-- MedicalRecord
    ClinicModel <|-- MedicalHistory
    ClinicModel <|-- Allergy
    ClinicModel <|-- Diagnosis
    ClinicModel <|-- Payment
    ClinicModel <|-- DoctorPayout
    ClinicModel <|-- Rating
    ClinicModel <|-- PatientPoints
    ClinicModel <|-- Notification

    %% ====================================================================
    %%  ONE-TO-ONE / POLYMORPHIC USER-TO-ROLE
    %% ====================================================================
    User "1" --> "0..1" Admin : has
    Admin "0..1" --> "1" User : belongs to

    User "1" --> "0..1" Secretary : has
    Secretary "0..1" --> "1" User : belongs to

    User "1" --> "0..1" Doctor : has
    Doctor "0..1" --> "1" User : belongs to

    User "1" --> "0..1" Patient : has
    Patient "0..1" --> "1" User : belongs to

    User "1" --> "0..*" Notification : has
    Notification "*" --> "1" User : belongs to

    %% ====================================================================
    %%  PATIENT RELATIONSHIPS
    %% ====================================================================
    Patient "1" --> "0..*" MedicalRecord : has
    MedicalRecord "*" --> "1" Patient : belongs to

    Patient "1" --> "0..*" Appointment : books
    Appointment "*" --> "1" Patient : belongs to

    Patient "1" --> "0..*" TreatmentPlan : assigned
    TreatmentPlan "*" --> "1" Patient : belongs to

    Patient "1" --> "0..*" TreatmentSession : attends
    TreatmentSession "*" --> "1" Patient : belongs to

    Patient "1" --> "0..*" ToothCondition : has
    ToothCondition "*" --> "1" Patient : belongs to

    Patient "1" --> "0..*" Payment : makes
    Payment "*" --> "1" Patient : belongs to

    Patient "1" --> "0..*" Rating : gives
    Rating "*" --> "1" Patient : belongs to

    Patient "1" --> "0..*" PatientPoints : earns
    PatientPoints "*" --> "1" Patient : belongs to

    Patient "1" --> "0..*" Diagnosis : receives
    Diagnosis "*" --> "1" Patient : belongs to

    %% ====================================================================
    %%  DOCTOR RELATIONSHIPS
    %% ====================================================================
    Doctor "1" --> "0..*" Appointment : handles
    Appointment "*" --> "1" Doctor : handled by

    Doctor "1" --> "0..*" TreatmentPlan : creates
    TreatmentPlan "*" --> "1" Doctor : created by

    Doctor "1" --> "0..*" TreatmentSession : performs
    TreatmentSession "*" --> "1" Doctor : performed by

    Doctor "1" --> "0..*" ToothCondition : diagnoses
    ToothCondition "*" --> "1" Doctor : diagnosed by

    Doctor "1" --> "0..*" DoctorPayout : receives
    DoctorPayout "*" --> "1" Doctor : received by

    Doctor "1" --> "0..*" Rating : receives
    Rating "*" --> "1" Doctor : received by

    Doctor "1" --> "0..*" Diagnosis : makes
    Diagnosis "*" --> "1" Doctor : made by

    %% ====================================================================
    %%  APPOINTMENT → TREATMENT SESSION
    %% ====================================================================
    Appointment "1" --> "0..*" TreatmentSession : generates
    TreatmentSession "*" --> "1" Appointment : belongs to

    %% ====================================================================
    %%  TREATMENT PLAN → STAGES
    %% ====================================================================
    TreatmentPlan "1" --> "0..*" TreatmentStage : contains
    TreatmentStage "*" --> "1" TreatmentPlan : belongs to

    %% ====================================================================
    %%  TREATMENT SESSION COMPOSITION (composition: child cannot exist without parent)
    %% ====================================================================
    TreatmentSession "1" *-- "0..*" ToothCondition : records
    TreatmentSession "1" *-- "0..*" TreatmentDetails : has
    TreatmentSession "1" *-- "0..*" Payment : receives
    TreatmentSession "1" *-- "0..*" DoctorPayout : generates
    TreatmentSession "1" *-- "0..*" Diagnosis : produces

    %% ====================================================================
    %%  TOOTH & DETAILS
    %% ====================================================================
    Tooth "1" --> "0..*" ToothCondition : has
    ToothCondition "*" --> "1" Tooth : refers to

    Tooth "1" --> "0..*" TreatmentDetails : referenced in
    TreatmentDetails "*" --> "1" Tooth : refers to

    %% ====================================================================
    %%  MEDICAL RECORD COMPOSITION
    %% ====================================================================
    MedicalRecord "1" *-- "0..*" MedicalHistory : contains
    MedicalRecord "1" *-- "0..*" Allergy : contains
    MedicalRecord "1" *-- "0..*" Diagnosis : contains

    %% ====================================================================
    %%  CONTROLLER → SERVICE (dependency)
    %% ====================================================================
    DoctorAuthController ..> DoctorAuthService : uses
    PatientAuthController ..> PatientAuthService : uses

    %% ====================================================================
    %%  SERVICE → REQUEST (dependency)
    %% ====================================================================
    DoctorAuthService ..> DoctorRegisterRequest : validates
    DoctorAuthService ..> DoctorLoginRequest : validates
    PatientAuthService ..> PatientRegisterRequest : validates
    PatientAuthService ..> PatientLoginRequest : validates

    %% ====================================================================
    %%  SERVICE → MODEL (dependency)
    %% ====================================================================
    DoctorAuthService ..> User : creates/finds
    DoctorAuthService ..> Doctor : creates
    PatientAuthService ..> User : creates/finds
    PatientAuthService ..> Patient : creates
```
