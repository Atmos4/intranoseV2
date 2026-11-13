<?php
restrict_access(Access::$EDIT_USERS);

$group_id = get_route_param("group_id", numeric: false);
$members = GroupService::getMembers($group_id, isset($_GET['desactives']));
if ($members === null) {
    force_404("this group does not exist");
}
$name = GroupService::isClub($group_id) ? GroupService::CLUB_NAME : em()->find(UserGroup::class, $group_id)->name;
$slug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $name))), '-');

header("Content-Type: text/csv; charset=utf-8");
header('Content-Disposition: attachment; filename="groupe-' . ($slug ?: 'export') . '.csv"');

$out = fopen("php://output", "w");
fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel detects the encoding
fputcsv($out, ["Nom", "Prénom", "Email", "Téléphone", "Statut", "Permission"], ";", '"', "");
foreach ($members as $member) {
    $guardian_emails = array_filter(
        array_map(fn($g) => $g->email, $member->guardians->toArray()),
        fn($e) => $e !== "",
    );
    fputcsv($out, [
        $member->last_name,
        $member->first_name,
        $member->real_email,
        $member->phone,
        $member->status->value,
        $member->permission->value
    ], ";", '"', "");
}
fclose($out);
exit;
