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

    static function create($slug = null): self|null
    {
        if ($slug == null) {
            $slug = self::getSelectedClubSlug();
        }
        return new self(DB::forClub($slug), $slug);
    }

    static function clubExists($slug)
    {
        return file_exists(club_data_path($slug));
    }

    public function getClub()
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

    public function deleteClub()
    {
        $deletedClubsDir = base_path() . "/.sqlite/.deleted_clubs"; // maybe change this later?
        mk_dir($deletedClubsDir, true);
        // soft delete
        return rename(club_data_path($this->slug), $deletedClubsDir . "/$this->slug" . date("YmdHis"));
    }

    public function updateClub(Club $c, $newName = null, $color = null, /* $newSlug = null */): Result
    {
        if ($newName) {
            $c->name = $newName;
            $c->themeColor = ThemeColor::from($color);
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
            mk_dir($path);
            return [];
        }
        $dirs = array_filter(scandir($path), fn($item)
            => is_dir($path . "/$item") && !in_array($item, ['.', '..', '.shared']));

        return array_values($dirs);
    }

    static function isClubSelectionAvailable()
    {
        return !env("SELECTED_CLUB");
    }

    static function getSelectedClubSlug()
    {
        $club = env("SELECTED_CLUB") ?? $_SESSION["selected_club"] ?? null;
        if ($club && !isset($_SESSION["selected_club_name"])) {
            if (!self::selectClub($club))
                return null;
        }
        return $club;
    }

    public function getSelectedClub()
    {
        $club_slug = ClubManagementService::getSelectedClubSlug();
        return $this->db->em()
            ->createQuery("SELECT c from Club c WHERE c.slug = :slug")
            ->setParameters(["slug" => $club_slug])
            ->getResult()[0];
    }

    static function selectClub($slug)
    {
        if (!self::clubExists($slug)) {
            return false;
        }
        $_SESSION["selected_club_name"] = ClubManagementService::create($slug)->getClub()->name;
        $_SESSION["selected_club"] = $slug;
        return true;
    }

    static function createNewClub($name, $slug): Result
    {
        $path = club_data_path($slug);
        if (file_exists($path)) {
            return Result::error("Club slug already exists");
        }
        try {
            $db = DB::forClub($slug);
            $em = $db->em();
            if (!SeedingService::applyMigrations($db))
                throw new ResultException("failed to apply migrations");

            $clubData = new Club($name, $slug);
            $em->persist($clubData);
            $em->flush();
        } catch (ResultException $e) {
            rm_rf($path);
            return Result::error("Error: " . $e->getMessage());
        }
        return Result::wrap($db, "Club created");
    }

    public function getClubColor($slug)
    {
        if (!$slug) {
            return null;
        }
        $c = $this->getClub();
        return $c->themeColor->value;
    }
}