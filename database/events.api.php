<?php
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;

function get_event_data($event_id, $user_id = null): Event
{
    $qb = em()->createQueryBuilder();
    $qb->select('e', 'ee')
        ->from(Event::class, 'e')
        ->leftJoin('e.entries', 'ee')
        ->leftJoin('ee.user', 'eu', Join::WITH, 'eu.id = :uid')
        ->leftJoin('e.races', 'r')
        ->leftJoin('r.entries', 're')
        ->leftJoin('re.user', 'ru', Join::WITH, 'ru.id = :uid')
        ->where('e.id = :eid')
        ->setParameters(['eid' => $event_id, 'uid' => $user_id]);
    try {
        return $qb->getQuery()
            ->getSingleResult();
    } catch (NoResultException) {
        force_404("this event does not exist");
    }
}

function persist_event(
    string $event_name,
    string $start_date,
    string $end_date,
    string $limit_date,
    Event|null $event = null
): int
{
    $event ??= new Event();
    $event->name = $event_name;
    $event->start_date = date_create($start_date);
    $event->end_date = date_create($end_date);
    $event->deadline = date_create($limit_date);
    em()->persist($event);
    em()->flush();
    return $event->id;
}

function get_file($id)
{
    return fetch_single(
        "SELECT * from circulaires WHERE id = ?",
        $id
    );
}

function set_file($event_id, $path, $date, $size, $mime)
{
    $result = query_db(
        "INSERT INTO circulaires(path, date, size, mime) VALUES (?,?,?,?) 
        ON DUPLICATE KEY UPDATE path = ?, date= ?, size = ?, mime = ?;",
        $path,
        $date,
        $size,
        $mime,
        $path,
        $date,
        $size,
        $mime
    );
    $circu_id = fetch_single("SELECT id 
    FROM circulaires 
    WHERE date = ?;", $date)[0];
    $result = $result && query_db("UPDATE deplacements 
    SET circu = ?
    WHERE did = ?
    LIMIT 1;", $circu_id, $event_id);
    return $result;
}