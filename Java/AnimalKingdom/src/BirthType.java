/* MAKE NO CHANGES TO THIS ENUM */
public enum BirthType {

	LIVE_BIRTH ("Live Birth"), LAYS_EGGS ("Lays Eggs");
	
	private String description;
	
	private BirthType(String description) {
		this.description = description;
	}
	
	@Override
	public String toString() {
		return this.description;
	}
	
}
