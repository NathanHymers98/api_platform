<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert; // To use symfony's built-in constraints




// The @ApiResource annotation exposes this class meaning that this class will be used for the API operations and functionality.

// The collectionOperations and itemOperations relate to which HTTP operations are going to be used by a collection of resources or a an individual resource.

// For example get is both a collection operation and a item operation because we will want to get a collection of resources, but we will also want to get an individual resource.

// If we didn't want users to delete a resource, then we would remove that from the annotation below and it would not appear on the Apiplaform documentation page.

// The shortName="cheeses" annotation changes the urls that relate to this resource class. If we did not add this it would automatically create a URL for us.

// The normalizationContext annotation - thanks to this annotation, when an object is being serialized it will only serialize fields that are inside the "{cheese_listing:read}" group

// The arrtibutes annotation adds pagination to our API, I am only allowing 10 resources per page and this applies to any filtering that the user does on the API. If there are more than 10 resources when the user tries to get the collection of resources
// it will only show 10 to them, and in order to see the all the resources they would have to go to the second, third, fourth etc. page to see them depending on how many resources we have.
// We can add different attributes in this annotation, for example I am adding which formats this CheeseListing class can be when getting a response back. I have added all the formats that are global, meaning that any other class
// can be formatted in these formats. I have added the extra bit to the csv format because I have not configured this format in api_platform.yaml because I do not want it to be global.

// The @Group annotations basically set that specific field to either a read group or a write group. The read group is what is being done in the background, the fields still exist and are being populated with data.
// however they are never seen by the user. The fields that are set to write, are fields that the user can see and interact with. For example, when creating a new cheese resource, we only want them to have access to fields
// like title, description etc. things that they can edit. We do not want them to edit fields like when the resource was created because that would make for a unsecure and bad API. We can set certain fields to be both read and write
// because it would make sense for them to be like this in our API.

// The @ApiFilter allows us to use filtering for a specific field. In the example below, I am using BooleanFilter because the field that I am allowing to be filtered is a boolean. Afterwards, I am targeting the property where the field comes from
// in this case it is the property isPublished. What this annotation allows a user to do is when they are trying to get either a collection or single cheese resource, they can filter the results between resources that are published, resources that
// are not published or just not set a filter at all if they want to see all the published and unpublished resources from the API

// The second @ApiFilter is a SearchFilter, this is because the property that we are trying to filter through is a string. This also means that it needs a bit more added to it in order to work as we want. After setting the property that we want to filter
// in this case title, we add a "partial" argument after it. This means that when the user types something into their search bar, it finds partial relations to other titles. For example if some resources had the title cheese and others blue cheese
// if the user type in the search box "blue" they would only get back the resources that had their search characters inside it. There are different configurations for this search aside from partial, although this one is the more used one because it is
// very friendly to the users. These other configurations are exact, start, end or on word start.

// The ProperFilter just like the ones above filters things out. In this case, since we don't want both the description itself and the short description to show up in the jsonld data when a user tries to get a single resource.
// Instead it would be better if they only saw the one that they needed to see. This, and the method getShortDescription() is what allows this to happen.

/**
 * @ApiResource(
 *     collectionOperations={"get", "post"},
 *     itemOperations={"get", "put", "delete"},
 *     normalizationContext={"groups"={"cheese_listing:read"}, "swagger_definition_name"="Read"},
 *     denormalizationContext={"groups"={"cheese_listing:write"}, "swagger_definition_name"="Write"},
 *     shortName="cheeses",
 *     attributes={
 *           "pagination_items_per_page"=10,
 *           "formats"={"jsonld", "json", "html", "jsonhal", "csv"={"text/csv"}}
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\CheeseListingRepository")
 * @ApiFilter(BooleanFilter::class, properties={"isPublished"})
 * @ApiFilter(SearchFilter::class, properties={"title": "partial", "description": "partial"})
 * @ApiFilter(RangeFilter::class, properties={"price"})
 * @ApiFilter(PropertyFilter::class)
 */
class CheeseListing
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    // The @Assert\NotBlank() and @AssertLength() (which is being passed arguments) apply validation to the title field when a user is trying to create a new cheese resource.
    // The arguments passed in @AssertLength() is what the min and max characters can be, and if the user goes over the max amount of characters, they get the maxMessage message to give them information on what they did wrong.
    // The @Assert\NotBlank() means that the user cannot leave it blank when creating a new resource
    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"cheese_listing:read", "cheese_listing:write"})
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min=2,
     *     max=50,
     *     maxMessage="Describe your cheese in 50 characters or less"
     * )
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Groups({"cheese_listing:read"})
     * @Assert\NotBlank()
     */
    private $description;

    /**
     * The price of this cheese in cents
     *
     * @ORM\Column(type="integer")
     * @Groups({"cheese_listing:read", "cheese_listing:write"})
     * @Assert\NotBlank()
     */
    private $price;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPublished = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="cheeseListings")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"cheese_listing:read", "cheese_listing:write"})
     */
    private $owner;

    public function __construct(string $title = null)
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->title = $title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @Groups("cheese_listing:read")
     */
    // This method below is what allows us to only show either a full description or a short description when a user gets a specific resource.
    public function getShortDescription(): ?string
    {
        if (strlen($this->description) < 40) { // If the description is already less than 40 characters
            return $this->description; // Just return it
        }

        return substr($this->description, 0, 40).'...'; // Otherwise return a sub string of the description, get the first 40 characters and put a "..." at the end.
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    // The @SerializedName changes the field name for the user to what is passed to it. Since this annotation applies to setTextDescription, this will now appear as just "description" to the user
    // instead of "textdescription" since before it just took away the set at the start of the method and put it in lower case
    /**
     * The description of the cheese as raw text
     *
     * @Groups({"cheese_listing:write"})
     * @SerializedName("description")
     */
    public function setTextDescription(string $description): self
    {
        $this->description = nl2br($description);

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * How long ago in text that this cheese listing was added
     *
     * @Groups({"cheese_listing:read"})
     */
    public function getCreatedAtAgo(): string // The ": string" and the end tells the api that this should return a string.
    {
        return Carbon::instance($this->getCreatedAt())->diffForHumans();
    }

    public function getIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
