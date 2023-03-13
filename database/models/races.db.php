<?php
class RaceEntry
{
    public string $race_id;
    public string $user_id;
    public bool $present;
    public bool $upgraded;
    public string $licence;
    public string $sport_ident;
    public string $comment;

    static function create(
        $race_id,
        $user_id,
        $present,
        $upgraded,
        $licence,
        $sport_ident,
        $comment
    ): RaceEntry
    {
        $new_entry = new RaceEntry();
        $new_entry->race_id = $race_id;
        $new_entry->user_id = $user_id;
        $new_entry->present = $present ?? false;
        $new_entry->upgraded = $upgraded ?? false;
        $new_entry->licence = $licence;
        $new_entry->sport_ident = $sport_ident;
        $new_entry->comment = $comment;
        return $new_entry;
    }

    private function exists_in_db(): bool
    {
        $existing = fetch("SELECT present FROM inscriptions_courses WHERE id_course=? AND id_runner = ?", $this->race_id, $this->user_id);
        return !!count($existing);
    }

    function save_in_db()
    {
        if ($this->exists_in_db()) {
            query_db(
                "UPDATE inscriptions_courses SET present=?,surclasse=?, licence=?, si=?, id_cat=?, rmq=? WHERE id_course=? AND id_runner=?",
                $this->present,
                $this->upgraded,
                $this->licence,
                $this->sport_ident,
                0,
                $this->comment,
                $this->race_id,
                $this->user_id
            );
        } else {
            query_db(
                "INSERT INTO inscriptions_courses(id_course, id_runner, present,surclasse, licence, si, id_cat, rmq) VALUES(?,?,?,?,?,?,?,?)",
                $this->race_id,
                $this->user_id,
                $this->present,
                $this->upgraded,
                $this->licence,
                $this->sport_ident,
                0,
                $this->comment
            );
        }
    }
}