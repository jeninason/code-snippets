
public class Goldfish extends Fish {

	public static final String GOLDFISH_DESCRIPTION = "Goldfish";
	
	public Goldfish(int id, String name) {
		super(id, name);
	}
	
	@Override
	public String getDescription() {
		return  super.getDescription() + GOLDFISH_DESCRIPTION;
	}

	
}
