
public class Parakeet extends Bird {

	public static final String PARAKEET_DESCRIPTION = "Parakeet";
	
	public Parakeet(int id, String name) {
		super(id, name);
	}
	
	@Override
	public String getDescription() {
		return super.getDescription() + PARAKEET_DESCRIPTION + " ("
				+ (canFly() ? "flies" : "does not fly")
				+ ")";
	}


}
