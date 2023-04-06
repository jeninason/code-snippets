
public class Horse extends Mammal{
	
	public static final String HORSE_DESCRIPTION = "Horse";
	
	public Horse(int id, String name) {
		super(id, name);
	}
	
	@Override
	public String getDescription() {
		return super.getDescription() + HORSE_DESCRIPTION;
	}
}
