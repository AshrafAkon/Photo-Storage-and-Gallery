package main

import (
	"fmt"
	"net/http"
	"os"

	"github.com/gin-gonic/gin"
	"github.com/h2non/bimg"
)

// album represents data about a record album.
type album struct {
	ID     string  `json:"id"`
	Title  string  `json:"title"`
	Artist string  `json:"artist"`
	Price  float64 `json:"price"`
}

// albums slice to seed record album data.
var albums = []album{
	{ID: "1", Title: "Blue Train", Artist: "John Coltrane", Price: 56.99},
	{ID: "2", Title: "Jeru", Artist: "Gerry Mulligan", Price: 17.99},
	{ID: "3", Title: "Sarah Vaughan and Clifford Brown", Artist: "Sarah Vaughan", Price: 39.99},
}

func main() {
	router := gin.Default()
	router.GET("/albums", getAlbums)
	router.GET("/albums/:id", getAlbumByID)
	router.POST("/albums", postAlbums)
	router.GET("/process", generateThumbnail)

	router.Run("localhost:8080")
}

// getAlbums responds with the list of all albums as JSON.
func getAlbums(c *gin.Context) {
	c.IndentedJSON(http.StatusOK, albums)
}

func generateThumbnail(c *gin.Context) {
	img_path := c.Param("img_path")
	save_path := c.Param("save_path")
	buffer, err := bimg.Read(img_path)
	if err != nil {
		fmt.Fprintln(os.Stderr, err)
	}

	image := bimg.NewImage(buffer)
	if err != nil {
		fmt.Fprintln(os.Stderr, err)
	}
	// Calculate the size for the thumbnail while maintaining the aspect ratio
	thumbnailSize, err := image.Size()
	if err != nil {
		fmt.Println("Error getting image size:", err)
		return
	}
	thumbnailSize.Width /= 10 // Adjust the width to your desired thumbnail size
	thumbnailSize.Height = 0  // Set the height to 0 to maintain aspect ratio

	// Resize the image to generate the thumbnail
	thumbnail, err := image.Resize(thumbnailSize.Width/10, 0)
	if err != nil {
		fmt.Println("Error resizing image:", err)
		return
	}
	// size, err := bimg.NewImage(newImage).Size()
	// if size.Width == 800 && size.Height == 600 {
	// 	fmt.Println("The image size is valid")
	// }

	bimg.Write(save_path, thumbnail)
	c.IndentedJSON(http.StatusCreated, albums)
}

// postAlbums adds an album from JSON received in the request body.
func postAlbums(c *gin.Context) {
	var newAlbum album

	// Call BindJSON to bind the received JSON to
	// newAlbum.
	if err := c.BindJSON(&newAlbum); err != nil {
		return
	}

	// Add the new album to the slice.
	albums = append(albums, newAlbum)
	c.IndentedJSON(http.StatusCreated, newAlbum)
}

// getAlbumByID locates the album whose ID value matches the id
// parameter sent by the client, then returns that album as a response.
func getAlbumByID(c *gin.Context) {
	id := c.Param("id")

	// Loop through the list of albums, looking for
	// an album whose ID value matches the parameter.
	for _, a := range albums {
		if a.ID == id {
			c.IndentedJSON(http.StatusOK, a)
			return
		}
	}
	c.IndentedJSON(http.StatusNotFound, gin.H{"message": "album not found"})
}
