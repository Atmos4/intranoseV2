<?php

class ClubManagementService
{

    function __construct(public DB $db, public string $slug)
    {
    }

    static function isLoggedIn()
    {
        // Refactor later to allow for multi user
        return isset($_SESSION["mgmt_authorized"]);
    }

    static function login($pw)
    {
        if ($pw != env("MGMT_PASSWORD"))
            return false;

        $_SESSION["mgmt_authorized"] = true;
        return true;

    }

    static function logout()
    {
        unset($_SESSION["mgmt_authorized"]);
    }

    static function fromSlug($slug): self|null
    {
        if (!file_exists(club_data_path($slug))) {
            return null;
        }
        return new self(new DB(SqliteFactory::clubPath($slug)), $slug);
    }

    function getClub(): Club
    {
        $results = $this->db->em()->createQuery("SELECT c FROM Club c")->getResult();
        if (!$results) {
            $club = new Club("", $this->slug);
            $this->db->em()->persist($club);
            $this->db->em()->flush();
            return $club;
        }
        return $results[0];
    }

    function deleteClub()
    {
        self::mkDir(base_path() . "/.sqlite/.deleted_clubs", true);
        // soft delete
        return rename(club_data_path($this->slug), base_path() . "/.sqlite/.deleted_clubs/$this->slug" . date("YmdHis"));
    }

    function updateClub(Club $c, $newName = null, /* $newSlug = null */): Result
    {
        if ($newName) {
            $c->name = $newName;
        }
        // note - unused for now as it can create bugs
        // if ($newSlug) {
        //     $r = self::renameDir($c->slug, $newSlug);
        //     if (!$r->success) {
        //         return $r;
        //     }
        //     $c->slug = $newSlug;
        // }
        $this->db->em()->flush();
        return Result::ok("Club updated");
    }

    static function listClubs(): array
    {
        $path = club_data_path();
        if (!is_dir($path)) {
            self::mkDir($path);
            return [];
        }
        $dirs = array_filter(scandir($path), fn($item)
            => is_dir($path . "/$item") && !in_array($item, ['.', '..']));

        return $dirs;
    }

    static function isClubSelectionAvailable()
    {
        return !env("PRODUCTION");
    }

    static function getSelectedClub()
    {
        return env("SELECTED_CLUB") ?? $_SESSION["selected_club"] ?? null;
    }

    static function selectClub($slug)
    {
        logger()->debug("ClubManagement: $slug selected");
        $_SESSION["selected_club_name"] = ClubManagementService::fromSlug($slug)->getClub()->name;
        $_SESSION["selected_club"] = $slug;
    }

    static function changeClubSelection()
    {
        unset($_SESSION["selected_club"]);
        unset($_SESSION["selected_club_name"]);
    }

    static function createNewClub($name, $slug): Result
    {
        $path = club_data_path($slug);
        if (file_exists($path)) {
            return Result::error("Club already exists");
        }

        $dbPath = "$path/db.sqlite";
        mkdir($path);
        try {
            $db = new DB($dbPath);
            $em = $db->em();
            if (!SeedingService::applyMigrations($db))
                throw new Error("failed to apply migrations");

            $clubData = new Club($name, $slug);
            $em->persist($clubData);
            $em->flush();
        } catch (\Throwable $th) {
            rm_rf($path);
            return Result::error("Error: " . $th->getMessage());
        }
        return Result::wrap($db, "Club created");
    }

    // -- helpers --

    private static function mkDir($path, $r = true)
    {
        if (!file_exists($path))
            mkdir($path, recursive: $r);
    }

    private static function renameDir($slug, $newSlug): Result
    {
        $newPath = club_data_path($newSlug);
        if (file_exists($newPath)) {
            return Result::error("Slug is already in use");
        }
        $path = club_data_path($slug);
        return rename($path, $newPath) ? Result::ok() : Result::error("Failed to rename slug directory from $slug to $newSlug");
    }
}