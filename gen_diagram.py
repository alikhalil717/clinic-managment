#!/usr/bin/env python3
"""Generate arch.drawio class diagram with proper UML relationship types."""

import os
import xml.etree.ElementTree as ET
import io

diagram_xml = '<mxfile host="65bd71144e">\n'
diagram_xml += '    <diagram id="class-diagram" name="Class Diagram">\n'
diagram_xml += '        <mxGraphModel dx="1665" dy="2047" grid="0" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" page="0" pageScale="1" pageWidth="2500" pageHeight="2000" math="0" shadow="0" adaptiveColors="simple">\n'
diagram_xml += '            <root>\n'
diagram_xml += '                <mxCell id="0"/>\n'
diagram_xml += '                <mxCell id="1" parent="0"/>\n\n'

# Helper functions
def box(id_val, label, x, y, w, h, color, text_color, desc=""):
    style = f'html=1;whiteSpace=wrap;verticalAlign=top;fillColor={color};strokeColor={text_color};strokeWidth=2;rounded=1;shadow=1;fontSize=10;fontFamily=Helvetica;'
    if desc:
        style += desc
    return f'                <mxCell id="{id_val}" value="{label}" style="{style}" parent="1" vertex="1">\n                    <mxGeometry x="{x}" y="{y}" width="{w}" height="{h}" as="geometry"/>\n                </mxCell>\n'

def edge(id_val, label, style_extra, src_x, src_y, tgt_x, tgt_y, points=None):
    style = f'edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeWidth=2;strokeColor=#333333;{style_extra};fontSize=11;'
    result = f'                <mxCell id="{id_val}" value="{label}" style="{style}" parent="1" edge="1">\n'
    result += f'                    <mxGeometry relative="1" as="geometry">\n'
    result += f'                        <mxPoint x="{src_x}" y="{src_y}" as="sourcePoint"/>\n'
    result += f'                        <mxPoint x="{tgt_x}" y="{tgt_y}" as="targetPoint"/>\n'
    if points:
        plist = "".join([f'<mxPoint x="{p[0]}" y="{p[1]}"/>' for p in points])
        result += f'                        <Array as="points">{plist}</Array>\n'
    result += f'                    </mxGeometry>\n                </mxCell>\n'
    return result

def edge_src(id_val, label, style_extra, src_id, tgt_id):
    style = f'edgeStyle=orthogonalEdgeStyle;rounded=0;orthogonalLoop=1;jettySize=auto;html=1;strokeWidth=2;strokeColor=#333333;{style_extra};fontSize=11;'
    result = f'                <mxCell id="{id_val}" value="{label}" style="{style}" parent="1" source="{src_id}" target="{tgt_id}" edge="1">\n'
    result += f'                    <mxGeometry relative="1" as="geometry"/>\n                </mxCell>\n'
    return result

def gen_edge(id_val, src_x, src_y, tgt_x, tgt_y, points=None):
    return edge(id_val, '&lt;i&gt;extends&lt;/i&gt;', 'endArrow=block;endSize=16;startArrow=none;startSize=12;fontSize=9;fontStyle=2', src_x, src_y, tgt_x, tgt_y, points)

def assoc_edge(id_val, label, src_x, src_y, tgt_x, tgt_y, points=None):
    return edge(id_val, label, 'endArrow=none;endSize=12;startArrow=none;startSize=12', src_x, src_y, tgt_x, tgt_y, points)

# Legend
diagram_xml += box('l-box', '&lt;b&gt;Legend&lt;/b&gt;&lt;hr&gt;&lt;font style=&quot;font-size:10px;&quot;&gt;\n&lt;b&gt;Association&lt;/b&gt; -------- &lt;i&gt;(plain line)&lt;/i&gt;&lt;br&gt;\n&lt;b&gt;Dependency&lt;/b&gt; - - - - -&gt; &lt;i&gt;(dashed+arrow)&lt;/i&gt;&lt;br&gt;\n&lt;b&gt;Generalization&lt;/b&gt; ------&gt; &lt;i&gt;(triangle head)&lt;/i&gt;&lt;br&gt;&lt;hr&gt;\n* Appointment/Treatment&lt;br&gt;\n* User/Auth&lt;br&gt;\n* Employees&lt;br&gt;\n* Patient&lt;br&gt;\n* Medical Records&lt;br&gt;\n* Financial/Other&lt;/font&gt;', '-450', '-1550', '260', '200', '#f5f5f5', '#666666')

# Legend examples
diagram_xml += edge('l-assoc', 'Association', 'endArrow=none;endSize=12;startArrow=none;startSize=12;fontSize=10', '-190', '-1495', '-40', '-1495')
diagram_xml += edge('l-dep', 'Dependency', 'endArrow=open;endSize=12;startArrow=none;startSize=12;dashed=1;fontSize=10', '-190', '-1465', '-40', '-1465')
diagram_xml += edge('l-gen', 'Generalization', 'endArrow=block;endSize=12;startArrow=none;startSize=12;fontSize=10', '-190', '-1435', '-40', '-1435')

# Abstract base class
diagram_xml += box('cm', '&lt;b&gt;&lt;i&gt;&amp;lt;&amp;lt;abstract&amp;gt;&amp;gt;&lt;br&gt;&lt;u&gt;ClinicModel&lt;/u&gt;&lt;/i&gt;&lt;/b&gt;&lt;hr&gt;&amp;lt;&amp;lt;extends Model&amp;gt;&amp;gt;&lt;hr&gt;# $guarded = []&lt;br&gt;# $timestamps = false', '140', '-1501', '230', '110', '#f5f5f5', '#666666', 'fontStyle=2')

# User
diagram_xml += box('user', '&lt;b&gt;&lt;u&gt;User&lt;/u&gt;&lt;/b&gt;&lt;hr&gt;&amp;lt;&amp;lt;Authenticatable&amp;gt;&amp;gt;&lt;hr&gt;+ user_id: int (PK)&lt;br&gt;+ first_name: string&lt;br&gt;+ last_name: string&lt;br&gt;+ email: string&lt;br&gt;+ phone: string&lt;br&gt;+ password: string&lt;br&gt;+ role: string&lt;br&gt;+ api_token: string?&lt;br&gt;+ created_at: timestamp&lt;hr&gt;+ admin(): HasOne&lt;br&gt;+ secretary(): HasOne&lt;br&gt;+ doctor(): HasOne&lt;br&gt;+ patient(): HasOne', '2435', '-855', '260', '260', '#dae8fc', '#6c8ebf')

# Admin
diagram_xml += box('admin', '&lt;b&gt;&lt;u&gt;Admin&lt;/u&gt;&lt;/b&gt;&lt;hr&gt;+ admin_id: int (PK, FK)&lt;hr&gt;+ user(): BelongsTo', '376', '-1021', '200', '90', '#fff2cc', '#d6b656')

# Secretary
diagram_xml += box('secretary', '&lt;b&gt;&lt;u&gt;Secretary&lt;/u&gt;&lt;/b&gt;&lt;hr&gt;+ secretary_id: int (PK, FK)&lt;hr&gt;+ user(): BelongsTo', '811', '-1021', '210', '90', '#fff2cc', '#d6b656')

# Doctor
diagram_xml += box('doctor', '&lt;b&gt;&lt;u&gt;Doctor&lt;/u&gt;&lt;/b&gt;&lt;hr&gt;+ doctor_id: int (PK, FK)&lt;br&gt;+ specialization: string&lt;br&gt;+ license_number: string&lt;br&gt;+ years_of_experience: int&lt;br&gt;+ rating: float&lt;br&gt;+ reviews_count: int&lt;hr&gt;+ user(): BelongsTo&lt;br&gt;+ appointments(): HasMany&lt;br&gt;+ treatmentPlans(): HasMany&lt;br&gt;+ treatmentSessions(): HasMany&lt;br&gt;+ toothConditions(): HasMany&lt;br&gt;+ payouts(): HasMany&lt;br&gt;+ ratings(): HasMany&lt;br&gt;+ diagnoses(): HasMany', '411', '-651', '260', '280', '#fff2cc', '#d6b656')

# Patient
diagram_xml += box('patient', '&lt;b&gt;&lt;u&gt;Patient&lt;/u&gt;&lt;/b&gt;&lt;hr&gt;+ patient_id: int (PK, FK)&lt;br&gt;+ date_of_birth: date&lt;hr&gt;+ user(): BelongsTo&lt;br&gt;+ medicalRecords(): HasMany&lt;br&gt;+ appointments(): HasMany&lt;br&gt;+ treatmentPlans(): HasMany&lt;br&gt;+ treatmentSessions(): HasMany&lt;br&gt;+ toothConditions(): HasMany&lt;br&gt;+ payments(): HasMany&lt;br&gt;+ ratings(): HasMany&lt;br&gt;+ points(): HasMany&lt;br&gt;+ diagnoses(): HasMany', '501', '-281', '270', '290', '#E1D5E7', '#9673a6')

# Appointment
diagram_xml += box('appt', '&lt;b&gt;&lt;u&gt;Appointment&lt;/u&gt;&lt;/b&gt;&lt;hr&gt;+ appointment_id: int (PK)&lt;br&gt;+ patient_id: int (FK)&lt;br&gt;+ doctor_id: int (FK)&lt;br&gt;+ appointment_date: datetime&lt;br&gt;+ status: string&lt;hr&gt;+ patient(): BelongsTo&lt;br&gt;+ doctor(): BelongsTo&lt;br&gt;+ treatmentSessions(): HasMany', '-749', '-1021', '240', '190', '#f8cecc', '#b85450')

# TreatmentPlan
diagram_xml += box('tp', '&lt;b&gt;&lt;u&gt;TreatmentPlan&lt;/u&gt;&lt;/b&gt;&lt;hr&gt;+ plan_id: int (PK)&lt;br&gt;+ patient_id: int (FK)&lt;br&gt;+ doctor_id: int (FK)&lt;br&gt;+ description: text&lt;hr&gt;+ patient(): BelongsTo&lt;br&gt;+ doctor(): BelongsTo&lt;br&gt;+ stages(): HasMany', '-459', '-861', '230', '170', '#f8cecc', '#b85450')

# TreatmentStage
diagram_xml += box('tstage', '&lt;b&gt;&lt;u&gt;TreatmentStage&lt;/u&gt;&lt;/b&gt;&lt;hr&gt;+ stage_id: int (PK)&lt;br&gt;+ plan_id: int (FK)&lt;br&gt;+ name: string&lt;br&gt;+ order: int', '-459', '-621', '220', '90', '#f8cecc', '#b85450')

# TreatmentSession
diagram_xml += box('tsession', '&lt;b&gt;&lt;u&gt;TreatmentSession&lt;/u&gt;&lt;/b&gt;&lt;hr&gt;+ session_id: int (PK)&lt;br&gt;+ appointment_id: int (FK)&lt;br&gt;+ doctor_id: int (FK)&lt;br&gt;+ patient_id: int (FK)&lt;br&gt;+ estimated_cost: decimal&lt;br&gt;+ notes: text&lt;hr&gt;+ appointment(): BelongsTo&lt;br&gt;+ doctor(): BelongsTo&lt;br&gt;+ patient(): BelongsTo&lt;br&gt;+ toothConditions(): HasMany&lt;br&gt;+ treatmentDetails(): HasMany&lt;br&gt;+ payments(): HasMany&lt;br&gt;+ payouts(): HasMany&lt;br&gt;+ diagnoses(): HasMany', '161', '-981', '260', '290', '#f8cecc', '#b85450')

# TreatmentDetails
diagram_xml += box('tdetails', '&lt;b&gt;&lt;u&gt;TreatmentDetails&lt;/u&gt;&lt;/b&gt;&lt;hr&gt;+ detail_id: int (PK)&lt;br&gt;+ session_id: int (FK)&lt;br&gt;+ tooth_id: int (FK)&lt;br&gt;+ description: text&lt;br&gt;+ cost: decimal&lt;hr&gt;+ session(): BelongsTo&lt;br&gt;+ tooth(): BelongsTo', '-559', '-441', '230', '160', '#f8cecc', '#b85450')

# Tooth
diagram_xml += box('tooth', '&lt;b&gt;&lt;u&gt;Tooth&lt;/u&gt;&lt;/b&gt;&lt;hr&gt;+ tooth_id: int (PK)&lt;br&gt;+ tooth_number: int&lt;br&gt;+ name: string', '-219', '-431', '210', '80', '#f8cecc', '#b85450')

# ToothCondition
diagram_xml += box('tcond', '&lt;b&gt;&lt;u&gt;ToothCondition&lt;/u&gt;&lt;/b&gt;&lt;hr&gt;+ condition_id: int (PK)&lt;br&gt;+ patient_id: int (FK)&lt;br&gt;+ tooth_id: int (FK)&lt;br&gt;+ doctor_id: int (FK)&lt;br&gt;+ session_id: int (FK)&lt;br&gt;+ diagnosis: text&lt;hr&gt;+ patient(): BelongsTo&lt;br&gt;+ tooth(): BelongsTo&lt;br&gt;+ doctor(): BelongsTo&lt;br&gt;+ session(): BelongsTo', '51', '-201', '240', '190', '#f8cecc', '#b85450')

# MedicalRecord
diagram_xml += box('mr', '&lt;b&gt;&lt;u&gt;MedicalRecord&lt;/u&gt;&lt;/b&gt;&lt;hr&gt;+ record_id: int (PK)&lt;br&gt;+ patient_id: int (FK)&lt;br&gt;+ created_date: date&lt;br&gt;+ description: text&lt;hr&gt;+ patient(): BelongsTo&lt;br&gt;+ histories(): HasMany&lt;br&gt;+ allergies(): HasMany&lt;br&gt;+ diagnoses(): HasMany', '1511', '-1181', '240', '170', '#ffe6cc', '#d79b00')

# MedicalHistory
diagram_xml += box('mh', '&lt;b&gt;&lt;u&gt;MedicalHistory&lt;/u&gt;&lt;/b&gt;&lt;hr&gt;+ history_id: int (PK)&lt;br&gt;+ record_id: int (FK)&lt;br&gt;+ condition: string&lt;br&gt;+ notes: text&lt;hr&gt;+ record(): BelongsTo', '1221', '-1021', '230', '130', '#ffe6cc', '#d79b00')

# Allergy
diagram_xml += box('allergy', '&lt;b&gt;&lt;u&gt;Allergy&lt;/u&gt;&lt;/b&gt;&lt;hr&gt;+ allergy_id: int (PK)&lt;br&gt;+ record_id: int (FK)&lt;br&gt;+ allergen: string&lt;br&gt;+ severity: string&lt;hr&gt;+ record(): BelongsTo', '861', '-1261', '230', '130', '#ffe6cc', '#d79b00')

# Diagnosis
diagram_xml += box('dx', '&lt;b&gt;&lt;u&gt;Diagnosis&lt;/u&gt;&lt;/b&gt;&lt;hr&gt;+ diagnosis_id: int (PK)&lt;br&gt;+ record_id: int (FK)&lt;br&gt;+ patient_id: int (FK)&lt;br&gt;+ doctor_id: int (FK)&lt;br&gt;+ session_id: int (FK)&lt;br&gt;+ diagnosis_text: text&lt;hr&gt;+ record(): BelongsTo&lt;br&gt;+ patient(): BelongsTo&lt;br&gt;+ doctor(): BelongsTo&lt;br&gt;+ session(): BelongsTo', '786', '-1081', '240', '220', '#ffe6cc', '#d79b00')

# Payment
diagram_xml += box('payment', '&lt;b&gt;&lt;u&gt;Payment&lt;/u&gt;&lt;/b&gt;&lt;hr&gt;+ payment_id: int (PK)&lt;br&gt;+ patient_id: int (FK)&lt;br&gt;+ related_session_id: int (FK)&lt;br&gt;+ amount: decimal&lt;br&gt;+ payment_date: datetime&lt;hr&gt;+ patient(): BelongsTo&lt;br&gt;+ relatedSession(): BelongsTo', '1751', '-901', '240', '170', '#d5e8d4', '#82b366')

# DoctorPayout
diagram_xml += box('dpayout', '&lt;b&gt;&lt;u&gt;DoctorPayout&lt;/u&gt;&lt;/b&gt;&lt;hr&gt;+ payout_id: int (PK)&lt;br&gt;+ doctor_id: int (FK)&lt;br&gt;+ session_id: int (FK)&lt;br&gt;+ amount: decimal&lt;br&gt;+ payout_date: datetime&lt;hr&gt;+ doctor(): BelongsTo&lt;br&gt;+ session(): BelongsTo', '2081', '-821', '240', '160', '#d5e8d4', '#82b366')

# Rating
diagram_xml += box('rating', '&lt;b&gt;&lt;u&gt;Rating&lt;/u&gt;&lt;/b&gt;&lt;hr&gt;+ rating_id: int (PK)&lt;br&gt;+ patient_id: int (FK)&lt;br&gt;+ doctor_id: int (FK)&lt;br&gt;+ score: int&lt;br&gt;+ comment: text&lt;hr&gt;+ patient(): BelongsTo&lt;br&gt;+ doctor(): BelongsTo', '1391', '-351', '240', '170', '#d5e8d4', '#82b366')

# PatientPoints
diagram_xml += box('ppoints', '&lt;b&gt;&lt;u&gt;PatientPoints&lt;/u&gt;&lt;/b&gt;&lt;hr&gt;+ point_id: int (PK)&lt;br&gt;+ patient_id: int (FK)&lt;br&gt;+ points: int&lt;br&gt;+ earned_date: datetime&lt;hr&gt;+ patient(): BelongsTo', '1691', '-641', '230', '130', '#d5e8d4', '#82b366')

# Notification
diagram_xml += box('notif', '&lt;b&gt;&lt;u&gt;Notification&lt;/u&gt;&lt;/b&gt;&lt;hr&gt;+ notification_id: int (PK)&lt;br&gt;+ user_id: int (FK)&lt;br&gt;+ message: text&lt;br&gt;+ is_read: bool&lt;br&gt;+ created_at: timestamp&lt;hr&gt;+ user(): BelongsTo', '2012', '-421', '230', '140', '#d5e8d4', '#82b366')

# ===== GENERALIZATION EDGES (endArrow=block - directional triangle) =====
# All models extend ClinicModel (x=255, y=-1391)

diagram_xml += gen_edge('g-admin', '476', '-1021', '255', '-1391', [('476', '-1391')])
diagram_xml += gen_edge('g-sec', '916', '-1021', '255', '-1391', [('916', '-1391')])
diagram_xml += gen_edge('g-doc', '541', '-651', '255', '-1391')
diagram_xml += gen_edge('g-pat', '636', '-281', '255', '-1391', [('636', '-1391')])
diagram_xml += gen_edge('g-appt', '-629', '-1021', '255', '-1391', [('-629', '-1391')])
diagram_xml += gen_edge('g-tp', '-344', '-861', '255', '-1391')
diagram_xml += gen_edge('g-ts', '291', '-981', '255', '-1391')
diagram_xml += gen_edge('g-tstage', '-349', '-621', '255', '-1391')
diagram_xml += gen_edge('g-tdetails', '-444', '-441', '255', '-1391')
diagram_xml += gen_edge('g-tooth', '-114', '-431', '255', '-1391')
diagram_xml += gen_edge('g-tcond', '171', '-201', '255', '-1391')
diagram_xml += gen_edge('g-mr', '1631', '-1181', '255', '-1391', [('1631', '-1391')])
diagram_xml += gen_edge('g-mh', '1336', '-1021', '255', '-1391', [('1336', '-1391')])
diagram_xml += gen_edge('g-allergy', '976', '-1261', '255', '-1391', [('976', '-1391')])
diagram_xml += gen_edge('g-dx', '906', '-1081', '255', '-1391')
diagram_xml += gen_edge('g-pay', '1871', '-901', '255', '-1391', [('1871', '-1391')])
diagram_xml += gen_edge('g-dp', '2201', '-821', '255', '-1391', [('2201', '-1391')])
diagram_xml += gen_edge('g-rat', '1511', '-351', '255', '-1391', [('1511', '-1391')])
diagram_xml += gen_edge('g-pp', '1806', '-641', '255', '-1391', [('1806', '-1391')])
diagram_xml += gen_edge('g-notif', '2127', '-421', '255', '-1391', [('2127', '-1391')])

# ===== ASSOCIATION EDGES (endArrow=none - plain line) =====
# with multiplicities and verb labels

nb = '&amp;#160;'  # non-breaking space

diagram_xml += assoc_edge('a-u-admin', f'1...1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}1...1&lt;br&gt;&lt;i&gt;is&lt;/i&gt;', '2435', '-780', '576', '-976', [('577', '-780')])
diagram_xml += assoc_edge('a-u-sec', f'1...1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}1...1&lt;br&gt;&lt;i&gt;is&lt;/i&gt;', '2555', '-855', '1021', '-976', [('2555', '-1112'), ('1021', '-1112')])
diagram_xml += assoc_edge('a-u-doc', f'1...1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}1...1&lt;br&gt;&lt;i&gt;is&lt;/i&gt;', '2540', '-855', '671', '-571', [('2540', '-571')])
diagram_xml += assoc_edge('a-u-pat', f'1...1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}1...1&lt;br&gt;&lt;i&gt;is&lt;/i&gt;', '2520', '-855', '771', '-241', [('2520', '-241')])
diagram_xml += edge_src('a-u-notif', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;has', 'endArrow=none;endSize=12;startArrow=none;startSize=12', 'user', 'notif')
diagram_xml += assoc_edge('a-doc-appt', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;has', '411', '-511', '-509', '-982', [('175', '-511'), ('175', '-982')])
diagram_xml += assoc_edge('a-doc-tp', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;creates', '411', '-560', '-229', '-810')
diagram_xml += assoc_edge('a-doc-ts', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;conducts', '411', '-630', '291', '-691', [('375', '-630'), ('375', '-691')])
diagram_xml += assoc_edge('a-doc-tc', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;diagnoses', '411', '-422', '291', '-201', [('411', '-422'), ('411', '-251'), ('291', '-251')])
diagram_xml += assoc_edge('a-doc-dp', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;receives', '671', '-451', '2081', '-751', [('671', '-451'), ('2081', '-451')])
diagram_xml += assoc_edge('a-doc-rat', f'*{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}1&lt;br&gt;rates', '671', '-391', '1391', '-291', [('671', '-391'), ('671', '-291')])
diagram_xml += assoc_edge('a-doc-dx', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;makes', '671', '-471', '1026', '-941', [('671', '-471'), ('1026', '-471'), ('1026', '-941')])
diagram_xml += assoc_edge('a-pat-appt', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;books', '501', '-241', '-509', '-931', [('-9', '-241'), ('-9', '-931')])
diagram_xml += assoc_edge('a-pat-mr', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;has', '636', '-281', '1511', '-1091', [('636', '-1091')])
diagram_xml += assoc_edge('a-pat-tp', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;has', '501', '-261', '-229', '-801', [('-229', '-261')])
diagram_xml += assoc_edge('a-pat-ts', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;attends', '771', '-261', '421', '-931', [('771', '-261'), ('421', '-261'), ('421', '-931')])
diagram_xml += assoc_edge('a-pat-tcond', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;has', '501', '-271', '291', '-201', [('501', '-201')])
diagram_xml += assoc_edge('a-pat-pay', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;pays', '636', '-281', '1751', '-831', [('636', '-831')])
diagram_xml += assoc_edge('a-pat-rat', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;gives', '771', '-271', '1391', '-321', [('771', '-271'), ('1391', '-271')])
diagram_xml += assoc_edge('a-pat-pp', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;earns', '771', '-301', '1691', '-581', [('771', '-301'), ('1691', '-301')])
diagram_xml += assoc_edge('a-pat-dx', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;receives', '771', '-301', '1026', '-1001', [('771', '-301'), ('1026', '-301'), ('1026', '-1001')])
diagram_xml += assoc_edge('a-appt-ts', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;results in', '-509', '-931', '161', '-870', [('-509', '-870')])
diagram_xml += assoc_edge('a-tp-ts', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;has', '-344', '-786', '-349', '-621', [('-344', '-786'), ('-349', '-786')])
diagram_xml += assoc_edge('a-ts-td', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;includes', '291', '-691', '-329', '-441', [('291', '-691'), ('-329', '-691')])
diagram_xml += assoc_edge('a-ts-tc', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;detects', '421', '-861', '291', '-201', [('421', '-861'), ('421', '-106'), ('291', '-106')])
diagram_xml += assoc_edge('a-ts-pay', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;generates', '421', '-800', '1751', '-800')
diagram_xml += assoc_edge('a-ts-dp', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;generates', '421', '-820', '2081', '-820')
diagram_xml += assoc_edge('a-ts-dx', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;yields', '421', '-920', '1026', '-920', [('421', '-920'), ('1026', '-920')])
diagram_xml += assoc_edge('a-td-tooth', f'*{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}1&lt;br&gt;references', '-329', '-421', '-219', '-411', [('-329', '-421'), ('-219', '-421')])
diagram_xml += assoc_edge('a-tooth-tc', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;has', '-9', '-431', '291', '-251', [('-9', '-431'), ('-9', '-251'), ('291', '-251')])
diagram_xml += assoc_edge('a-mr-mh', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;contains', '1511', '-1091', '1451', '-1021', [('1511', '-1091'), ('1451', '-1091')])
diagram_xml += assoc_edge('a-mr-allergy', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;contains', '1511', '-1151', '1091', '-1221', [('1511', '-1151'), ('1091', '-1151')])
diagram_xml += assoc_edge('a-mr-dx', f'1{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}{nb}*&lt;br&gt;contains', '1511', '-1121', '1026', '-1041', [('1511', '-1121'), ('1026', '-1121')])

# Close XML
diagram_xml += '            </root>\n'
diagram_xml += '        </mxGraphModel>\n'
diagram_xml += '    </diagram>\n'
diagram_xml += '</mxfile>\n'

# Validate XML
ET.parse(io.StringIO(diagram_xml))
print("XML is valid!")

# Write file
filepath = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'arch.drawio')
with open(filepath, 'w') as f:
    f.write(diagram_xml)
print(f"Written {len(diagram_xml)} bytes to {filepath}")
