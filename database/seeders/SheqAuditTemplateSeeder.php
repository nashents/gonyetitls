<?php

namespace Database\Seeders;

use App\Models\SheqAuditItem;
use App\Models\SheqAuditSection;
use App\Models\SheqAuditTemplate;
use Illuminate\Database\Seeder;

class SheqAuditTemplateSeeder extends Seeder
{
    /**
     * Seed a generic Integrated Management System (IMS) internal audit
     * checklist aligned with ISO 9001:2015, ISO 14001:2015 & ISO 45001:2018.
     * Organizations can clone/adjust it from the Audit Templates screen.
     */
    public function run(): void
    {
        $template = SheqAuditTemplate::updateOrCreate(
            ['name' => 'IMS Internal Audit Checklist (ISO 9001 / 14001 / 45001)'],
            [
                'standard' => 'ISO 9001:2015, ISO 14001:2015, ISO 45001:2018',
                'description' => 'Generic integrated management system internal audit checklist. Grading: NC - Non Conformity, OFI - Opportunity for Improvement, C - Conformity.',
                'is_active' => 1,
            ]
        );

        $sections = [
            [
                'code' => '4.0', 'title' => 'Organisational Context', 'items' => [
                    ['4.1', 'Understanding the organisation and its context', 'Have internal and external issues affecting the intended outcomes of the management system been identified (e.g. using SWOT / PESTEL)? Are climate-related issues considered? Is the team aware of strategic issues relating to their activities?', 8],
                    ['4.2', 'Needs and expectations of workers and other interested parties', 'Has the department identified interested parties and their requirements? Check the stakeholder register, evidence of engagements and up-to-date service level agreements. Are needs that become compliance obligations determined, monitored and adhered to?', 7],
                    ['4.3', 'Scope of the management system', 'Are departmental boundaries and areas of responsibility defined with objective evidence? Have areas of influence been identified?', 3],
                    ['4.4', 'Management system and its processes', 'Is the process flow chart adequate and comprehensive: sources of inputs, inputs, activities (start to end), outputs and receivers of outputs (internal and external)?', 2],
                ],
            ],
            [
                'code' => '5.0', 'title' => 'Leadership', 'items' => [
                    ['5.1', 'Leadership and commitment', 'Are HODs chairing SHEQ meetings consistently and presenting departmental SHEQ performance? Check evidence of leadership engagements / walks and close-out of resulting actions. Are customer requirements determined, understood and consistently met? Check the customer complaints register.', 12],
                    ['5.2', 'Policy', 'Is the current SHEQ/IMS policy displayed and available to all employees, contractors and interested parties? Is it communicated, understood and applied? Are employees aware of the policy commitments?', 4],
                    ['5.3', 'Organisational roles, responsibilities and authorities', 'Check validity of appointments, signed appointment letters, organograms and SHEQ responsibilities. Is the appointment register up to date? Are appointments specific to the job function?', 6],
                    ['5.4', 'Consultation and participation of workers', 'Check worker participation in SHEQ activities: hazard identification and risk assessment, setting objectives, determination of training needs, incident and NC investigations, and SHEQ meetings.', 4],
                ],
            ],
            [
                'code' => '6.0', 'title' => 'Planning', 'items' => [
                    ['6.1', 'Actions to address risks and opportunities', 'Are hazard/aspect and risk/impact/opportunity registers available and up to date (baseline risk assessments and risk profiles)? Are issue-based risk assessments conducted for significant changes and continuous (pre-task) risk assessments performed? Are climate-related risks identified?', 10],
                    ['6.2', 'Effectiveness of controls', 'Are actions and controls to address risks, opportunities and environmental impacts being closed out? Is the effectiveness of controls formally evaluated? Are blind spots identified and managed?', 10],
                    ['6.3', 'Communication of risks', 'Are risks, opportunities and environmental aspects communicated across all levels? Are employees aware and knowledgeable?', 5],
                    ['6.4', 'Compliance obligations', 'Are compliance obligations identified? Check the compliance obligations register (permits, licences, agreements, exemptions and regulator guidelines). Does it include obligations arising from interested parties?', 5],
                    ['6.5', 'Evaluation of compliance obligations', 'Are compliance evaluations conducted (departmental self-audits)? Is compliance status monitored and discussed in departmental meetings? Are actions taken to address non-compliances?', 6],
                    ['6.6', 'Objectives and planning to achieve them', 'Are previous period objectives and programmes closed out? Are current objectives and programmes in place, consistent with policy, measurable, monitored, communicated and updated? Are compliance obligations considered?', 8],
                ],
            ],
            [
                'code' => '7.0', 'title' => 'Support', 'items' => [
                    ['7.1', 'Resources', 'Are adequate resources provided: people, infrastructure, ICT, operating environment and knowledge? Are monitoring and measurement resources suitable, maintained and calibrated/verified? Check equipment calibration status.', 10],
                    ['7.2', 'Competence', 'Has the department determined the necessary competence of workers? Check the competence matrix and compliance to it, training needs identification, training records, and measures for new hires, contractors and re-assigned employees.', 5],
                    ['7.3', 'Awareness', 'Check awareness of: policy, objectives, significant/top risks and aspects, benefits of improved performance, implications of non-conformance, and incidents with outcomes of investigations.', 5],
                    ['7.4', 'Communication and participation', 'Is there a system to communicate with interested parties and customers? Are incidents communicated? Check notice boards, talks, campaigns, newsletters, suggestion system, monthly committee meetings and employee participation.', 5],
                    ['7.5', 'Documented information', 'Are all SHEQ documents current and controlled? Are there uncontrolled or obsolete documents in use? Check availability, periodic reviews, revisions, approval and retrievability of documents.', 5],
                ],
            ],
            [
                'code' => '8.0', 'title' => 'Operation', 'items' => [
                    ['8.1.1', 'Operational planning and control', 'Are the department\'s top SHEQ risks identified with an up-to-date list? Are identified controls formally evaluated for effectiveness? Check availability, adequacy and validity of operational procedures and work instructions, and adherence to critical controls.', 8],
                    ['8.1.2.1', 'Top safety risks and critical controls', 'Are critical controls identified (e.g. in bowties)? Demonstrate linkage of critical controls with procedures and baseline risk assessments. For each risk, check implementation and effectiveness of mitigation measures.', 10],
                    ['8.1.2.2', 'Top health risks and critical controls', 'Are the critical controls identified and known? Check baseline risk assessments and procedures. For each risk, check implementation and effectiveness of mitigation measures.', 10],
                    ['8.1.2.3', 'Top environmental risks and critical controls', 'Are the critical controls identified and known? For each risk, check implementation and effectiveness of mitigation. Are emission/discharge points compliant with regulatory requirements?', 10],
                    ['8.1.2.4', 'Top quality risks and critical controls', 'Are the critical controls identified and known? Check baseline risk assessments and procedures. For each risk, check implementation and effectiveness of mitigation measures.', 10],
                    ['8.1.2.5', 'Field checks - other environmental controls', 'Field check operational controls: air (dust, gas, noise), biodiversity, energy use, hydrocarbons (spillages, storage), land (erosion, topsoil), waste (segregation, dumping), water (leakage, effluent discharge), bund walls and oil separators.', 5],
                    ['8.1.2.6', 'General hygiene and cleanliness', 'General hygiene of surroundings and working environment including offices (floors, walls, ablutions).', 4],
                    ['8.1.2.7', 'Hazardous chemical substances (HCS)', 'Check closure of previous HCS audit actions, alphabetical HCS list with SDS for all substances, appointment of an HCS coordinator, training of handlers, required licences, compatibility matrix, bunded and labelled storage, and spill containment provisions.', 6],
                    ['8.1.2.8', 'Lifting equipment', 'Is the procedure available and known? Is the equipment register up to date with quarterly inspections? Physical checks: colour coding, unique numbers, pre-use inspections, SWL markings, valid load test certificates, defect-free equipment and safety catches on hooks.', 16],
                    ['8.1.2.9', 'Fall protection / working at heights', 'Is the procedure available and known? Is the fall-arrest equipment register (including fixed anchors) available? Is a rescue plan in place? Physical checks: barricading and signage, substitute barriers where handrails removed, quarterly inspections of ladders and walkways, compliant scaffolding erected by competent persons.', 18],
                    ['8.1.2.10', 'Equipment and machinery safeguarding', 'Is the procedure available and a responsible person appointed? Physical checks: guards on all moving parts, risk-based guard inspections, no service gaps, guards secured and colour coded to standard.', 16],
                    ['8.1.2.11', 'Isolation and stored energy', 'Is the procedure available and known, with planned job observations? Physical checks: isolation capability for all energy sources, labelled isolation points, tagging system and warning signage.', 10],
                    ['8.1.2.12', 'Flammable gases and explosion prevention', 'Are training records available for authorised handlers and responsibilities defined? Physical checks: gas monitoring devices tested/calibrated, contraband control in explosive environments, storage areas free of incompatible materials, cylinders secured upright with safety collars.', 14],
                    ['8.1.2.13', 'Other operational controls', 'PPE issuance and control, medical surveillance, control of non-conforming products, housekeeping, pressure vessels, electrical safety, equipment and tool inspections/registers, and permit to work.', 9],
                    ['8.1.3', 'Management of change', 'Is the change management procedure implemented? Have all significant changes been identified with documentation, issue-based risk assessments and closure of action plans? Are changes noted during activities captured in pre-task risk assessments?', 4],
                    ['8.1.4', 'Procurement', 'Are procurement procedures in place, known and complied with? Are deviations raised as NCs and closed out? Are risks of new products/services assessed? Is delivered equipment/material to specification?', 6],
                    ['8.1.5', 'Contractor management', 'Check contractor onboarding documentation, induction and training, pre-engagement screening, inspections and audits with closure of action plans, and management of non-conforming contractor equipment through NCs.', 5],
                    ['8.2', 'Emergency preparedness and response', 'Are potential emergencies identified consistent with the baseline risk assessment? Are mock drills conducted with findings communicated and closed? Is the emergency team trained and a coordinator appointed? Physical checks: awareness of response plans, displayed emergency numbers and team members, emergency equipment provided and inspected, signage and evacuation routes.', 21],
                    ['8.4', 'Control of externally provided processes, products and services', 'Do externally provided processes, products and services conform to requirements? Are controls for external providers defined, with adequate information, verification and performance monitoring?', 3],
                    ['8.5', 'Production and service provision', 'Identification and traceability of outputs, care of customer/external provider property, preservation of outputs, and post-delivery activities including statutory requirements and customer feedback.', 6],
                    ['8.6', 'Release of products and services', 'Is there evidence of conformity with acceptance criteria and traceability to the person authorising release?', 3],
                    ['8.7', 'Control of nonconforming outputs', 'Are nonconforming outputs identified and controlled to prevent unintended use or delivery?', 4],
                ],
            ],
            [
                'code' => '9.0', 'title' => 'Performance Evaluation', 'items' => [
                    ['9.1.1', 'Monitoring, measurement, analysis and evaluation', 'Is there a monitoring plan (what is monitored and frequency)? Check occupational hygiene survey reports and action plans, injury/incident rates and trending, environmental monitoring (water, energy, waste, oil balances), formal handling of deviations, and evaluations of compliance.', 5],
                    ['9.1.2', 'Measuring equipment', 'Status of calibration or verification of measuring equipment; protection of equipment from damage and deterioration.', 5],
                    ['9.1.3', 'Customer satisfaction', 'Is monitoring of customer perceptions in place (surveys, feedback, meetings, compliments, reports)? Are analysis and evaluation of monitoring data used for decision making?', 5],
                    ['9.1.4', 'Occupational hygiene', 'Are all applicable occupational stressors identified and monitored per schedule (illumination, noise, dust, heat, vibration)? Are survey findings closed within stipulated time? Check ergonomics of workstations and furniture.', 10],
                    ['9.2.1', 'Internal audit results and closure', 'Are previous audit results available with action plans drawn and implemented? Check closure of previous internal and external audit actions.', 5],
                    ['9.2.2', 'Closure of other audit action plans', 'Check action plans from cross-functional audits, regulator inspections, insurer/underwriter audits and external assurance audits for closure.', 8],
                    ['9.3', 'Management review', 'Evidence of management system reviews at departmental/divisional level covering SHEQ performance, objectives, incidents and NC statistics, complaints, audit findings and compliance evaluation results, with closure of action items.', 4],
                ],
            ],
            [
                'code' => '10.0', 'title' => 'Improvement', 'items' => [
                    ['10.1', 'General improvement', 'Implementation of correction, corrective action, opportunities for improvement, breakthrough change, innovation and re-organisation.', 3],
                    ['10.2.1', 'Reporting of incidents and non-conformities', 'Reporting of incidents/accidents and non-conformities including stop-and-fix reports. Check incident/accident and NC registers.', 5],
                    ['10.2.2', 'Investigation', 'Investigation of accidents/incidents and non-conformities. Verify that required personnel visited the scene where applicable.', 7],
                    ['10.2.3', 'Action plans', 'Are action plans addressing causes of incidents/NCs appropriate? Is the effectiveness of action plans reviewed?', 5],
                    ['10.2.4', 'Records', 'Are incident/accident and NC records retained?', 5],
                ],
            ],
        ];

        $sectionSort = 10;
        foreach ($sections as $sectionData) {
            $section = SheqAuditSection::updateOrCreate(
                [
                    'sheq_audit_template_id' => $template->id,
                    'code' => $sectionData['code'],
                ],
                [
                    'title' => $sectionData['title'],
                    'sort_order' => $sectionSort,
                ]
            );
            $sectionSort += 10;

            $itemSort = 10;
            foreach ($sectionData['items'] as $item) {
                SheqAuditItem::updateOrCreate(
                    [
                        'sheq_audit_section_id' => $section->id,
                        'code' => $item[0],
                    ],
                    [
                        'requirement' => $item[1],
                        'guidance' => $item[2],
                        'possible_mark' => $item[3],
                        'sort_order' => $itemSort,
                    ]
                );
                $itemSort += 10;
            }
        }
    }
}
