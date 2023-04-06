
public class Kiwi extends Bird implements Endangered {

	public static final String KIWI_DESCRIPTION = "Kiwi";
	
	public Kiwi(int id, String name) {
		super(id, name);
	}
	
	@Override
	public boolean canFly() {
		return false;
	}
	@Override
	public String getDescription() {
		return  super.getDescription() + " " + KIWI_DESCRIPTION + " ("			
				+ (canFly() ? "flies" : "does not fly")
				+ ", endangered)";
	}

	@Override
	public void displayConservationInformation() {
		System.out.println(getName()  + " says: \"Help save the adorable " + KIWI_DESCRIPTION + "!\"");
		
	}
	
}
