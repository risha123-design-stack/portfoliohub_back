<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'profession',
        'career_goal',
        'package_name',
        'package_status',
        'package_activated_at',
        'role',
        'is_active',
        'location',
        'website',
        'current_position',
        'experience_years',
        'bio',
        'github',
        'linkedin',
        'facebook',
        'instagram',
        'twitter',
        'profile_photo',
        'password_changed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' =>
                'datetime',
            'password' =>
                'hashed',
            'is_active' =>
                'boolean',
            'package_activated_at' =>
                'datetime',
            'password_changed_at' =>
                'datetime',
        ];
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'role' =>
                $this->role ?? 'user',
            'package' =>
                $this->package_name
                ?? 'Silver',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(
            Profile::class
        );
    }

    public function portfolioProfile(): HasOne
    {
        return $this->hasOne(
            PortfolioProfile::class
        );
    }

    public function portfolioAbout(): HasOne
    {
        return $this->hasOne(
            PortfolioAbout::class
        );
    }

    public function contactInformation(): HasMany
    {
        return $this
            ->hasMany(
                ContactInformation::class
            )
            ->orderBy('display_order')
            ->orderBy('id');
    }

    public function projects(): HasMany
    {
        return $this
            ->hasMany(Project::class)
            ->orderByDesc('featured')
            ->orderByDesc('start_date')
            ->orderByDesc('id');
    }

    public function educations(): HasMany
    {
        return $this
            ->hasMany(Education::class)
            ->orderBy('display_order')
            ->orderByDesc('start_date')
            ->orderByDesc('id');
    }

    public function experiences(): HasMany
    {
        return $this
            ->hasMany(Experience::class)
            ->orderBy('display_order')
            ->orderByDesc('start_date')
            ->orderByDesc('id');
    }

    public function skills(): HasMany
    {
        return $this
            ->hasMany(Skill::class)
            ->orderByDesc('is_featured')
            ->orderBy('display_order')
            ->orderBy('id');
    }

    public function certificates(): HasMany
    {
        return $this
            ->hasMany(Certificate::class)
            ->orderByDesc('is_featured')
            ->orderBy('display_order')
            ->orderByDesc('issue_date')
            ->orderByDesc('id');
    }

    public function achievements(): HasMany
    {
        return $this
            ->hasMany(Achievement::class)
            ->orderByDesc('is_featured')
            ->orderBy('display_order')
            ->orderByDesc(
                'achievement_date'
            )
            ->orderByDesc('id');
    }

    public function languages(): HasMany
    {
        return $this
            ->hasMany(Language::class)
            ->orderByDesc('is_featured')
            ->orderBy('display_order')
            ->orderBy('id');
    }

    public function resumes(): HasMany
    {
        return $this
            ->hasMany(Resume::class)
            ->orderByDesc('is_primary')
            ->orderByDesc('updated_at')
            ->orderByDesc('id');
    }

    public function socialLinks(): HasMany
    {
        return $this
            ->hasMany(SocialLink::class)
            ->orderByDesc('is_featured')
            ->orderBy('display_order')
            ->orderBy('id');
    }

    public function codingProfiles(): HasMany
    {
        return $this->hasMany(
            CodingProfile::class
        );
    }

    public function techStacks(): HasMany
    {
        return $this->hasMany(
            TechStack::class
        );
    }

    public function developerProjects(): HasMany
    {
        return $this->hasMany(
            DeveloperProject::class
        );
    }

    public function teachingSubjects(): HasMany
    {
        return $this->hasMany(
            TeachingSubject::class
        );
    }

    public function workshops(): HasMany
    {
        return $this->hasMany(
            Workshop::class
        );
    }

    public function cameraEquipment(): HasMany
    {
        return $this->hasMany(
            CameraEquipment::class
        );
    }

    public function photographyPortfolios(): HasMany
    {
        return $this->hasMany(
            PhotographyPortfolio::class
        );
    }

    public function academicProjects(): HasMany
    {
        return $this->hasMany(
            AcademicProject::class
        );
    }

    public function studentCertifications(): HasMany
    {
        return $this->hasMany(
            StudentCertification::class
        );
    }

    public function clientProjects(): HasMany
    {
        return $this->hasMany(
            ClientProject::class
        );
    }

    public function clientTestimonials(): HasMany
    {
        return $this->hasMany(
            ClientTestimonial::class
        );
    }

    public function caseStudies(): HasMany
    {
        return $this->hasMany(
            CaseStudy::class
        );
    }

    public function designTools(): HasMany
    {
        return $this->hasMany(
            DesignTool::class
        );
    }

    public function clinicalExperiences(): HasMany
    {
        return $this->hasMany(
            ClinicalExperience::class
        );
    }

    public function medicalCertifications(): HasMany
    {
        return $this->hasMany(
            MedicalCertification::class
        );
    }

    public function contentPortfolios(): HasMany
    {
        return $this->hasMany(
            ContentPortfolio::class
        );
    }

    public function socialMediaProfiles(): HasMany
    {
        return $this->hasMany(
            SocialMediaProfile::class
        );
    }

    public function portfolioPublication(): HasOne
    {
        return $this->hasOne(
            PortfolioPublication::class
        );
    }

    public function portfolioAnalytics(): HasMany
    {
        return $this->hasMany(
            PortfolioAnalytic::class
        );
    }

    public function settings(): HasOne
    {
        return $this->hasOne(
            UserSetting::class
        );
    }
    public function payments(): HasMany
{
    return $this->hasMany(
        Payment::class
    );
}

public function processedPayments(): HasMany
{
    return $this->hasMany(
        Payment::class,
        'processed_by'
    );
}
public function sendPasswordResetNotification($token): void
    {
        $this->notify(
            new ResetPasswordNotification($token)
        );
    }
}
