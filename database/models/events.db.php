<?php

class EventEntry
{
    public string $event_id;
    public string $user_id;
    public bool $present;
    public bool $transport;
    public bool $accomodation;
    public string $date;
    public string $comment;

    function __construct(
        $event_id,
        $user_id,
        $present,
        $transport,
        $accomodation,
        $date,
        $comment
    )
    {
        $this->event_id = $event_id;
        $this->user_id = $user_id;
        $this->present = $present;
        $this->transport = $transport;
        $this->accomodation = $accomodation;
        $this->date = $date;
        $this->comment = $comment;
    }

    static function create(
        $event_id,
        $user_id,
        $present,
        $transport,
        $accomodation,
        $date,
        $comment
    )
    {
        return new EventEntry(
            $event_id,
            $user_id,
            $present ?? false,
            $transport ?? false,
            $accomodation ?? false,
            $date,
            $comment
        );
    }

    private function exists_in_db(): bool
    {
        $existing = fetch("SELECT present FROM inscriptions_depl WHERE id_depl=? AND id_runner = ?", $this->event_id, $this->user_id);
        return !!count($existing);
    }

    function save_in_db()
    {
        if ($this->exists_in_db()) {
            query_db(
                "UPDATE inscriptions_depl SET present=?,transport=?, heberg=?, date=?, comment=? WHERE id_depl=? AND id_runner=?",
                $this->present,
                $this->transport,
                $this->accomodation,
                $this->date,
                $this->comment,
                $this->event_id,
                $this->user_id
            );
        } else {
            query_db(
                "INSERT INTO inscriptions_depl(id_depl, id_runner, present, transport, heberg, date, comment) VALUES(?,?,?,?,?,?,?)",
                $this->event_id,
                $this->user_id,
                $this->present,
                $this->transport,
                $this->accomodation,
                $this->date,
                $this->comment
            );
        }
    }

    function to_form()
    {
        return
            [
                "event_entry" => $this?->present ?? null,
                "event_transport" => $this?->transport ?? null,
                "event_accomodation" => $this?->accomodation ?? null,
                "event_comment" => $this?->comment ?? null,
            ];
    }
}

class Event
{
    public string $id;
    public string $name;
    public string $start;
    public string $end;
    public string $deadline;
    public bool $open;
    public string $file_id;
    public ?EventEntry $entry;

    function __construct(
        string $id,
        string $name,
        string $start,
        string $end,
        string $deadline,
        bool $open,
        string $file_id,
        ?EventEntry $entry
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->start = $start;
        $this->end = $end;
        $this->deadline = $deadline;
        $this->open = $open;
        $this->file_id = $file_id;
        $this->entry = $entry;
    }

    static function single_from_db(string $event_id, ?string $user_id = null): Event
    {
        $entry = null;
        if ($user_id) {
            $result = fetch_single(
                "SELECT deplacements.*, depl.* FROM deplacements 
                LEFT JOIN inscriptions_depl as depl
                    ON depl.id_depl = deplacements.did 
                    AND depl.id_runner = ?
                WHERE did = ?
                ORDER BY depart DESC LIMIT 1;",
                $user_id,
                $event_id
            );

            $entry = $result["id_depl"] ? new EventEntry(
                $result["id_depl"],
                $result["id_runner"],
                $result["present"],
                $result["transport"],
                $result["heberg"],
                $result["date"],
                $result["comment"]
            ) : null;

        } else {
            $result = fetch_single("SELECT * FROM deplacements
            WHERE did = ?
            ORDER BY depart DESC LIMIT 1;",
                $event_id
            );
        }

        return new Event(
            $event_id,
            htmlspecialchars_decode($result["nom"], ENT_QUOTES),
            $result["depart"],
            $result["arrivee"],
            $result["limite"],
            $result["open"],
            $result["circu"],
            $entry
        );
    }
}