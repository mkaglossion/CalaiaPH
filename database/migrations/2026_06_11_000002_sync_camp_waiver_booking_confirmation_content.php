<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('booking_confirmation_contents')->updateOrInsert(
            ['booking_type' => 'campwaiver'],
            [
                'page_eyebrow' => 'Booking Confirmation',
                'page_title' => 'Camp Waiver',
                'page_description' => 'Manage the camp waiver shown to staycation guests before payment.',
                'next_steps_title' => 'What\'s Next?',
                'next_steps' => json_encode([
                    'Review the waiver title and body shown before checkout.',
                    'Keep the wording aligned with the full camp waiver document.',
                ]),
                'support_title' => 'Need assistance?',
                'support_description' => 'Reach us anytime if you need help with your booking content.',
                'support_email' => 'calaia.ph@gmail.com',
                'support_phone' => '+63 905 129 4741',
                'home_button_label' => null,
                'print_button_label' => null,
                'payment_button_label' => null,
                'camp_waiver_title' => 'Camp Waiver and Release from Liability',
                'camp_waiver_body' => <<<'TEXT'
CAMP WAIVER AND RELEASE FROM LIABILITY

By accomplishing this form, the guest freely and voluntarily gives his/her consent to provide necessary and relevant personal data for the management of the facility and to reasonably process the same for records purposes and other relevant matters. Furthermore, the guest acknowledges that his/her personal data may also be shared to third persons if there is a reasonable need to do so, such as in cases of emergency. Personal data gathered shall be deleted from the files within one (1) year.

The guest is also aware that the facility is provided with CCTV cameras which may capture video or photos of guests within the vicinity for security and other related purposes.

Participant's Information:

Full Name of Camper:
Date of Birth:
Gender:
Address:
Parent/Guardian Name (if applicable):
Emergency Contact Name:
Emergency Contact Phone Number:
Relationship to Participant:
Kindly state any medical or physical condition that you have:

I. ACKNOWLEDGMENT OF RISKS
1. I, the undersigned, acknowledge and am fully aware that the activities involved in participating in the camp activities involve inherent risks. These risks include, but are not limited to, physical injury, illness, emotional distress, or death due to strenuous activities such as:
- Physical activities (for example: hiking, swimming, archery, climbing, sports)
- Environmental conditions (for example: uneven terrain, weather)
- Camp equipment (for example: ropes, climbing gear, water sports equipment)
- Interaction with other participants
- Potential for allergic reactions (for example: food, insects, plants)
- Animal or insect bites or stings
- Participation in outdoor adventures or challenges
2. I understand that these activities are physically demanding and may involve risks that cannot be fully eliminated even with proper training and supervision.
3. This Waiver and Release from Liability covers all activities within the park. By signing this form, the guest acknowledges that the owners, management, or employees of Calaia Nature Park have no fault whatsoever for any injury, damage, or death that the guest may suffer while participating in the camp's activities.

II. ASSUMPTION OF RISK
I voluntarily and freely assume all risks associated with participating in the camp activities, including participation by my child, children, or ward if applicable. I understand that accidents and injuries can occur. I agree to take full responsibility for any injury, illness, or damage that I may incur, or that my child, children, or ward may incur, whether caused by my own recklessness, negligence, lack of ability, or the actions of others.

III. RELEASE OF LIABILITY
In consideration of being allowed to participate in camp activities, I, for myself, my heirs, executors, administrators, and assigns, do hereby release, waive, discharge, and hold harmless Calaia Nature Park, its owners, employees, agents, volunteers, and representatives from any and all liability for any injury, illness, death, or property damage arising out of my participation or the participation of my child, children, or ward in camp activities, whether caused by the negligence of others or the personnel which includes the employees and management of Calaia Nature Park or otherwise. I agree not to bring any legal action or claim against Calaia Nature Park for any injury, damage, or loss sustained during participation in camp activities.

IV. MEDICAL AUTHORIZATION
In the event that I or my child, children, or ward require medical treatment during my or our stay or visit in the camp, I authorize Calaia Nature Park to find a way to transport myself or my child, children, or ward to the nearest medical center either via private or public vehicle. I understand that I will be financially responsible for any medical costs incurred.

I further agree to disclose or inform the camp staff of any medical condition, allergies, or special needs that I or my child, children, or ward knowingly have which may prevent us from participating in the activities.

V. HEALTH AND FITNESS
I confirm that I, my child, children, or ward is in good physical and mental health to participate in the camp activities.

I take it upon my responsibility to disclose any physical or mental condition that I have or my child, children, or ward have that may prevent us from participating in the camp activities.

VI. PHOTO/VIDEO RELEASE
I consent to the use of photographs, videos, or other media taken during our participation in camp activities for promotional, marketing, or educational purposes. I understand that I will not receive any compensation for such use and waive any right to review or approve the final product.

VII. PARENTAL/GUARDIAN CONSENT (for Minors)
I, the undersigned parent/legal guardian, give my permission for my child/children or ward to participate in the camp activities. I agree to assume full responsibility for my child/children or ward's safety and well-being during their participation in the camp activities and hold Calaia Nature Park, its employees, and management harmless from any liability, as stated above.

VIII. RULES AND REGULATIONS
I agree to fully abide by all the rules and guidelines provided by Calaia Nature Park, including safety instructions and camp regulations. In the event that I or my child/children or ward fails to abide or follow the rules and guidelines provided by the management, I and/or my child/children or ward will be prevented from further joining the camp activities. If through my repeated reckless or negligent act, or my child/children or ward's act, that puts ourselves or the life of others in danger after having been called out by the management or its representative, I shall be asked to immediately leave the facility without refund.

IX. GOVERNING LAW
This waiver and release of liability shall be governed by and construed in accordance with existing laws. Any dispute will be resolved exclusively in the court of Batangas having jurisdiction over the subject matter, to the exclusion of all other courts.

X. SEVERABILITY
If any part of this waiver is found to be invalid, unenforceable, or unconstitutional, the remaining provisions that are valid shall remain in full force and effect.

XI. ACKNOWLEDGMENT AND SIGNATURE
I acknowledge that I have read and understood this waiver and release of liability. I understand that by affixing my signature in this document, I am waiving certain legal rights, including the right to sue.

Name and Signature of Guest:
Date:

If guest is under 18 (Parent/Guardian to sign):
Name and Signature of Parent/Guardian:
Name of Child/Ward:
Date:
TEXT
                ,
                'email_subject' => null,
                'email_greeting' => null,
                'email_intro' => null,
                'email_closing' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        // Intentionally left as a no-op so existing admin-edited waiver text is not destroyed on rollback.
    }
};
